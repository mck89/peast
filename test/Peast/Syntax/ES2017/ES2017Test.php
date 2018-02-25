<?php
namespace Peast\test\Syntax\ES2017;

class ES2017Test extends \Peast\test\Syntax\ES2016\ES2016Test
{
    protected $parser = "ES2017";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017");
    }
    
    protected function getExcludedTests()
    {
        return array(
            "CallExpression/Invalid6.js",
            "Functions/InvalidArguments.js",
            "ArrowFunction/Invalid5.js"
        );
    }
    
    public function keywordIdentifierProvider()
    {
        return array_merge(
            parent::keywordIdentifierProvider(),
            array(
                array("var await = 1", true, true),
                array("a.await", true, true),
                array("var async = 1", true, true),
                array("a.async", true, true),
                array("async function test(await){}", false, false),
                array("async function test(){var await}", false, false)
            )
        );
    }
}