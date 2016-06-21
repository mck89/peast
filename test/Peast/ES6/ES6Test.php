<?php
namespace test\Peast\ES6;

class ES6Test extends \test\Peast\TestBase
{
    public function jsTestFilesProvider()
    {
        return parent::getJsTestFiles(__DIR__);
    }
    
    /**
     * @dataProvider jsTestFilesProvider
     */
    public function testParser($sourceFile, $compareFile)
    {
        $options = array(
            "sourceType" => strpos($sourceFile, "modules") !== false ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT
        );
        $source = file_get_contents($sourceFile);
        $parser = \Peast\Peast::ES6($source, $options);
        $tree = $parser->parse();
        $this->compareJSFile($tree, $compareFile);
    }
    
    public function invalidJsTestFilesProvider()
    {
        return parent::getJsTestFiles(__DIR__, true);
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
        $parser = \Peast\Peast::ES6($source, $options);
        $tree = $parser->parse();
    }
}