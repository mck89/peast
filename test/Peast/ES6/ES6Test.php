<?php
namespace test\Peast\ES6;

class ES6Test extends \test\Peast\TestBase
{
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
        $tree = \Peast\Peast::ES6($source, $options)->parse();
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
        $tree = \Peast\Peast::ES6($source, $options)->tokenize();
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
        \Peast\Peast::ES6($source, $options)->parse();
    }
    
    public function testParseEmptySource()
    {
        $tree = \Peast\Peast::ES6("")->parse();
        $this->assertTrue($tree->getType() === "Program");
        $this->assertSame(0, count($tree->getBody()));
    }
    
    public function testTokenizeEmptySource()
    {
        $tree = \Peast\Peast::ES6("")->tokenize();
        $this->assertSame(0, count($tree));
    }
}