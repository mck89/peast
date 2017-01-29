<?php
namespace test\Peast\ES6;

class ES6Test extends \test\Peast\TestBase
{
    protected $parser = "ES6";
    
    protected function getTestVersions()
    {
        return array("ES6");
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
        $options = array(
            "sourceType" => strpos($sourceFile, "modules") !== false ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT
        );
        $source = file_get_contents($sourceFile);
        $tree = \Peast\Peast::{$this->parser}($source, $options)->parse();
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
        $options = array(
            "sourceType" => strpos($sourceFile, "modules") !== false ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT
        );
        $source = file_get_contents($sourceFile);
        $tree = \Peast\Peast::{$this->parser}($source, $options)->tokenize();
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
        $options = array(
            "sourceType" => strpos($sourceFile, "modules") !== false ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT
        );
        $source = file_get_contents($sourceFile);
        \Peast\Peast::{$this->parser}($source, $options)->parse();
    }
    
    public function testParseEmptySource()
    {
        $tree = \Peast\Peast::{$this->parser}("")->parse();
        $this->assertTrue($tree->getType() === "Program");
        $this->assertSame(0, count($tree->getBody()));
    }
    
    public function testTokenizeEmptySource()
    {
        $tree = \Peast\Peast::{$this->parser}("")->tokenize();
        $this->assertSame(0, count($tree));
    }
    
    public function keywordIdentifierProvider()
    {
        return array(
            array("try{}catch(yield){}", true, false),
            array("while(true){continue yield}", true, false),
            array("while(true){break yield}", true, false),
            array("function yield(){}", true, false),
            array("class yield{}", false, false),
            array("var yield", true, false),
            array("let yield", true, false),
            array("export {interface as yield}", true, true),
            array("import yield from 'source'", false, false),
            array("[a, ...yield] = b", true, false),
            array("var a = {yield:1, if:2, true:3}", true, true),
            array("a.yield.true.if", true, true),
            array("yield.abc", true, false)
        );
    }
    
    /**
     * @dataProvider keywordIdentifierProvider
     */
    public function testKeywordIdentifier($code, $valid, $validStrictMode)
    {
        $options = array(
            "sourceType" => preg_match("#import|export#", $code) ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT
        );
        foreach (array($valid, $validStrictMode) as $strict => $isValid) {
            $exCode = ($strict ? '"use strict";' : '') . $code;
            $validResult = true;
            try {
                \Peast\Peast::{$this->parser}($exCode, $options)->parse();
            } catch (\Exception $ex) {
                $validResult = false;
            }
            $this->assertSame($isValid, $validResult);
        }
    }
}