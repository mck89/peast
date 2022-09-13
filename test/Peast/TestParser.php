<?php
namespace Peast\test;

abstract class TestParser extends TestBase
{ 
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
                foreach ($compare as $k => $v) {
                    $this->objectTestRecursive($v, $obj->$k, $tokens, "$message" . "->$k");
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
     * @dataProvider invalidJsTestFilesProvider
     */
    public function testParserException($sourceFile)
    {
        $this->expectException('Peast\Syntax\Exception');

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
        ),
        "exportedNameInExportAll"=> array(
            array("ES2020", "files", "modules", "ExportDeclaration", "ExportAllNamespace.js")
        ),
        "importMeta"=> array(
            array("ES2020", "files", "modules", "ImportMeta", "ImportMeta.js")
        ),
        "coalescingOperator"=> array(
            array("ES2020", "files", "CoalescingOperator", "CoalescingOperator.js")
        ),
        "optionalChaining"=> array(
            array("ES2020", "files", "OptionalChaining", "OptionalChainingMemberExp.js")
        ),
        "logicalAssignmentOperators"=> array(
            array("ES2021", "files", "LogicalAssignmentOperators", "LogicalAssignmentOperators.js")
        ),
        "numericLiteralSeparator"=> array(
            array("ES2021", "files", "NumericLiteralSeparator", "NumericLiteralSeparator.js")
        ),
        "privateMethodsAndFields"=> array(
            array("ES2022", "files", "PrivateMethods", "PrivateMethods.js"),
            array("ES2022", "files", "PrivateMethods", "PrivateMemberExpression.js"),
        ),
        "classFieldsPrivateIn"=> array(
            array("ES2022", "files", "ClassFields", "PrivateClassFieldIn.js")
        ),
        "topLevelAwait"=> array(
            array("ES2022", "files", "modules", "AsyncAwait", "TopLevelAwait.js")
        ),
        "classStaticBlock"=> array(
            array("ES2022", "files", "ClassStaticBlock", "ClassStaticBlock.js")
        ),
        "arbitraryModuleNSNames"=> array(
            array("ES2022", "files", "modules", "ExportDeclaration", "ArbitraryNamespaceName.js"),
            array("ES2022", "files", "modules", "ImportDeclaration", "ArbitraryNamespaceName.js")
        ),
        "hashbangComments"=> array(
            array("ES2023", "files", "HashbangComments", "HashbangComments.js")
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
     * @dataProvider invalidFutureFeaturesProvider
     */
    public function testFutureFeaturesParsingFail($feature, $sourceFile)
    {
        $this->expectException('Peast\Syntax\Exception');

        if ($feature === null) {
            throw new \Peast\Syntax\Exception("Nothing to test", new \Peast\Syntax\Position(0, 0, 0));
        } else {
            $this->instanceParser($sourceFile)->parse();
        }
    }
}