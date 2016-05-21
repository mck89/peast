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
        $module = strpos($sourceFile, "modules") !== false;
        $parser = new \Peast\Syntax\ES6\Parser($module);
        $tree = \Peast\Peast::fromFile($parser, $sourceFile);
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
        $module = strpos($sourceFile, "modules") !== false;
        $parser = new \Peast\Syntax\ES6\Parser($module);
        \Peast\Peast::fromFile($parser, $sourceFile);
    }
}