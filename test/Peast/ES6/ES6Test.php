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
        $parser = new \Peast\Syntax\ES6\Parser();
        $tree = \Peast\Peast::fromFile($parser, $sourceFile);
        $this->compareJSFile($tree, $compareFile);
    }
}