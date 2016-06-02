<?php
namespace test\Peast;

class TestBase extends \PHPUnit_Framework_TestCase
{
    protected function getJsTestFiles($dir, $invalid = false)
    {
        $ds = DIRECTORY_SEPARATOR;
        $testFiles = array();
        $files = array_merge(
            glob($dir . $ds . "files" . $ds . "*" . $ds . "*.js"),
            glob($dir . $ds . "files" . $ds . "*" . $ds . "*" . $ds . "*.js")
        );
        foreach ($files as $jsFile) {
            $isInvalid = strpos($jsFile, "Invalid");
            $parts = explode($ds, $jsFile);
            $testName = implode($ds, array_slice($parts, -2));
            if ($isInvalid && $invalid) {
                $testFiles[$testName] = array($jsFile);
            } elseif (!$isInvalid && !$invalid) {
                $testFiles[$testName] = array(
                    $jsFile,
                    str_replace(".js", ".json", $jsFile)
                );
            }
        }
        return $testFiles;
    }
    
    protected function compareJSFile($tree, $compareFile)
    {
        $compareTree = json_decode(file_get_contents($compareFile));
        $this->objectTestRecursive($compareTree, $tree);
    }
    
    protected function objectTestRecursive($compare, $obj, $message = "")
    {
        $objType = gettype($obj);
        $this->assertSame(gettype($compare), $objType, "gettype($message)");
        switch ($objType)
        {
            case "object":
                if (isset($compare->type)) {
                    $this->fixComparison($compare);
                }
                foreach ($compare as $k => $v) {
                    $fn = "get" . ucfirst($k);
                    $objValue = $obj->$fn();
                    $objValue = $this->fixParenthesizedExpression($objValue);
                    $this->objectTestRecursive($v, $objValue, "$message" . "->$k");
                }
            break;
            case "array":
                $this->assertSame(count($compare), count($obj), "count($message)");
                foreach ($compare as $k => $v) {
                    $this->objectTestRecursive($v, $obj[$k], "$message" . "[$k]");
                }
            break;
            default:
                $this->assertSame($compare, $obj, $message);
            break;
        }
    }
    
    protected function fixParenthesizedExpression($val)
    {
        if (is_object($val) &&
            $val instanceof \Peast\Syntax\Node &&
            $val->getType() === "ParenthesizedExpression") {
            return $this->fixParenthesizedExpression($val->getExpression());
        }
        return $val;
    }
    
    protected function fixComparison($compare)
    {
        //Fix location
        if (isset($compare->loc)) {
            $compare->location = $compare->loc;
            $compare->location->start->index = $compare->range[0];
            $compare->location->end->index = $compare->range[1];
            unset($compare->loc);
            unset($compare->range);
        }
        
        //Fix properties
        switch ($compare->type) {
            case "TryStatement":
                unset($compare->guardedHandlers);
                unset($compare->handlers);
            break;
            case "FunctionDeclaration":
            case "FunctionExpression":
            case "ArrowFunctionExpression":
                for ($i = 0; $i < count($compare->params); $i++) {
                    if (!isset($compare->defaults[$i]) ||
                        $compare->defaults[$i] === null) {
                        continue;
                    }
                    $compare->params[$i] = (object) array(
                        "type" => "AssignmentPattern",
                        "left" => $compare->params[$i],
                        "right" => $compare->defaults[$i]
                    );
                }
                unset($compare->defaults);
                unset($compare->expression);
            break;
            case "TemplateElement":
                $compare->rawValue = $compare->value->raw;
                $compare->value = $compare->value->cooked;
            break;
            case "Literal":
                if (isset($compare->regex)) {
                    $compare->type = "RegExpLiteral";
                    $compare->pattern = $compare->regex->pattern;
                    $compare->flags = $compare->regex->flags;
                    unset($compare->regex);
                    $compare->value = $compare->raw;
                }
            break;
            case "ForInStatement":
                unset($compare->each);
            break;
        }
    }
}