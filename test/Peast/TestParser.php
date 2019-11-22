<?php
namespace Peast\test;

abstract class TestParser extends TestBase
{ 
    protected $tokensTestProps = array("type", "value", "location");
    
    protected $tokensIdentifiersAsKeywords = array(
        "implements", "interface", "package", "private", "protected", "public",
        "static"
    );
    
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
    
    public function instanceParser($sourceFile)
    {
        $module = strpos($sourceFile, "modules") !== false;
        $jsx = strpos($sourceFile, "JSX") !== false;
        $options = array(
            "sourceType" => $module ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT,
            "jsx" => $jsx
        );
        return \Peast\Peast::{$this->parser}(file_get_contents($sourceFile), $options);
    }
    
    public function jsParserTestFilesProvider()
    {
        return parent::getJsTestFiles();
    }
    
    /**
     * @dataProvider jsParserTestFilesProvider
     */
    public function testParser($sourceFile, $compareFile)
    {
        $tree = $this->instanceParser($sourceFile)->parse();
        $this->compareJSFile($tree, $compareFile);
    }
    
    public function jsTokenizerTestFilesProvider()
    {
        return parent::getJsTestFiles(self::JS_TOKENIZE);
    }
    
    /**
     * @dataProvider jsTokenizerTestFilesProvider
     */
    public function testTokenizer($sourceFile, $compareFile)
    {
        $tree = $this->instanceParser($sourceFile)->tokenize();
        $this->compareJSFile($tree, $compareFile, true);
    }
    
    public function invalidJsTestFilesProvider()
    {
        return parent::getJsTestFiles(self::JS_INVALID);
    }
    
    /**
     * @expectedException \Peast\Syntax\Exception
     * @dataProvider invalidJsTestFilesProvider
     */
    public function testParserException($sourceFile)
    {
        $this->instanceParser($sourceFile)->parse();
    }
    
    protected $featuresTests = array(
        "exponentiationOperator"=> array(
            array("ES2016", "files", "ExponentiationOperator", "Assignment.js"),
            array("ES2016", "files", "ExponentiationOperator", "Simple.js")
        ),
        "asyncAwait"=> array(
            array("ES2017", "files", "AsyncAwait", "ArrowFunction.js"),
            array("ES2017", "files", "AsyncAwait", "ClassMethod.js"),
            array("ES2017", "files", "AsyncAwait", "FunctionExpression.js"),
            array("ES2017", "files", "AsyncAwait", "FunctionDeclaration.js")
        ),
        "trailingCommaFunctionCallDeclaration"=> array(
            array("ES2017", "files", "CallExpression", "TrailingComma.js"),
            array("ES2017", "files", "Functions", "ArgumentsTrailingComma.js")
        ),
        "forInInitializer"=> array(
            array("ES2017", "files", "ForStatement", "ForInAssign.js")
        ),
        "asyncIterationGenerators"=> array(
            array("ES2018", "files", "AsyncAwait", "AsyncGeneratorDeclaration.js"),
            array("ES2018", "files", "AsyncAwait", "AsyncGeneratorExpression.js"),
            array("ES2018", "files", "AsyncAwait", "AsyncGeneratorMethod.js"),
            array("ES2018", "files", "AsyncAwait", "ForAwait.js")
        ),
        "restSpreadProperties"=> array(
            array("ES2018", "files", "ObjectBinding", "Rest.js"),
            array("ES2018", "files", "ObjectBinding", "Spread.js")
        ),
        "skipEscapeSeqCheckInTaggedTemplates"=> array(
            array("ES2018", "files", "Templates", "TaggedWrongEscapeSequence.js")
        ),
        "optionalCatchBinding"=> array(
            array("ES2019", "files", "TryStatement", "CatchNoParam.js")
        ),
        "dynamicImport"=> array(
            array("ES2020", "files", "ImportExpression", "ImportExpression.js")
        ),
        "bigInt"=> array(
            array("ES2020", "files", "BigIntLiteral", "BigIntLiteral.js")
        )
    );
    
    public function invalidFutureFeaturesProvider()
    {
        $featuresClass = "\\Peast\\Syntax\\" . $this->parser . "\\Features";
        $features = new $featuresClass;
        $ret = array();
        $ds = DIRECTORY_SEPARATOR;
        $dir = __DIR__ . $ds . "Syntax" . $ds;
        foreach ($this->featuresTests as $feature => $tests) {
            if (!$features->$feature) {
                foreach ($tests as $test) {
                    $ret []= array($feature, $dir . implode($ds, $test));
                }
            }
        }
        if (!count($ret)) {
            $ret []= array(null, null);
        }
        return $ret;
    }
    
    /**
     * @expectedException \Peast\Syntax\Exception
     * @dataProvider invalidFutureFeaturesProvider
     */
    public function testFutureFeaturesParsingFail($feature, $sourceFile)
    {
        if ($feature === null) {
            throw new \Peast\Syntax\Exception("Nothing to test", new \Peast\Syntax\Position(0, 0, 0));
        } else {
            $this->instanceParser($sourceFile)->parse();
        }
    }
}