<?php
namespace Peast\test;

abstract class TestBase extends TestCaseBase
{
    const JS_INVALID = 1;
    
    const JS_PARSE = 2;
    
    const JS_TOKENIZE = 3;
    
    const JS_RENDERER = 4;
    
    protected $tokensTestProps = array("type", "value", "location");
    
    protected $tokensIdentifiersAsKeywords = array(
        "implements", "interface", "package", "private", "protected", "public",
        "static"
    );
    
    protected function getTestVersions() {}
    
    protected function getExcludedTests() {return array();}
    
    protected function getJsTestFiles($jsType = self::JS_PARSE)
    {
        $invalid = $jsType === self::JS_INVALID;
        $ds = DIRECTORY_SEPARATOR;
        $testFiles = array();
        $files = array();
        $dir = __DIR__;
        foreach ($this->getTestVersions() as $version) {
            $files = array_merge(
                $files,
                glob($dir . $ds . "Syntax" . $ds . $version . $ds . "files" . $ds . "*" . $ds . "*.js"),
                glob($dir . $ds . "Syntax" . $ds . $version . $ds . "files" . $ds . "*" . $ds . "*" . $ds . "*.js")
            );
        }
        $excludedTests = array_flip($this->getExcludedTests());
        foreach ($files as $jsFile) {
            $isInvalid = strpos($jsFile, "Invalid");
            $parts = explode($ds, $jsFile);
            $testName = implode("/", array_slice($parts, -2));
            if (isset($excludedTests[$testName])) {
                continue;
            }
            if ($isInvalid && $invalid) {
                $testFiles[$testName] = array($jsFile);
            } elseif (!$isInvalid && !$invalid) {
                if ($jsType === self::JS_TOKENIZE) {
                    $replacement = ".Tokens.json";
                    $op = "Tokenize";
                } elseif ($jsType === self::JS_RENDERER) {
                    $replacement = ".Render.txt";
                    $op = "Render";
                } else {
                    $replacement = ".json";
                    $op = "Parse";
                }
                $testFiles["$op $testName"] = array(
                    $jsFile,
                    str_replace(".js", $replacement, $jsFile)
                );
            }
        }
        return $testFiles;
    }
    
    protected function compareJSFile($tree, $compareFile, $tokens = false)
    {
        $compareTree = json_decode(file_get_contents($compareFile));
        $origTree = json_decode(json_encode($tree));
        $this->objectTestRecursive($compareTree, $origTree, $tokens);
    }
    
    protected function objectTestRecursive($compare, $obj, $tokens, $message = "")
    {
        $objType = gettype($obj);
        $this->assertSame(gettype($compare), $objType, "gettype($message)");
        switch ($objType)
        {
            case "object":
                if (isset($compare->type)) {
                    $this->fixComparison($compare, $tokens);
                }
                foreach ($compare as $k => $v) {
                    if ($tokens && isset($compare->type) && !in_array($k, $this->tokensTestProps)) {
                        continue;
                    }
                    $objValue = $obj->$k;
                    $objValue = $this->fixParenthesizedExpression($objValue);
                    $this->objectTestRecursive($v, $objValue, $tokens, "$message" . "->$k");
                }
            break;
            case "array":
                $this->assertSame(count($compare), count($obj), "count($message)");
                foreach ($compare as $k => $v) {
                    $this->objectTestRecursive($v, $obj[$k], $tokens, "$message" . "[$k]");
                }
            break;
            default:
                $this->assertSame($compare, $obj, $message);
            break;
        }
    }
    
    protected function fixParenthesizedExpression($val)
    {
        if (is_object($val) && isset($val->type) &&
            $val->type === "ParenthesizedExpression") {
            return $this->fixParenthesizedExpression($val->expression);
        }
        return $val;
    }
    
    protected function fixComparison($compare, $tokens)
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
                if ($compare->type !== "ArrowFunctionExpression") {
                    unset($compare->expression);
                }
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
            case "AssignmentPattern":
                unset($compare->operator);
            break;
            case "Identifier":
                if ($tokens &&
                    in_array($compare->value, $this->tokensIdentifiersAsKeywords)) {
                    $compare->type = "Keyword";
                }
            break;
        }
    }
}