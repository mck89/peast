<?php
namespace Peast\test\Syntax\ES2018;

class ES2018Test extends \Peast\test\Syntax\ES2017\ES2017Test
{
    protected $parser = "ES2018";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018");
    }
    
    protected function getExcludedTests()
    {
        $excluded = parent::getExcludedTests();
        return array_merge(
            $excluded,
            array(
                "AsyncAwait/InvalidAsync2.js",
                "AsyncAwait/InvalidAsync5.js"
            )
        );
    }
}