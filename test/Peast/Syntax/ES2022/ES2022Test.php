<?php
namespace Peast\test\Syntax\ES2022;

class ES2022Test extends \Peast\test\Syntax\ES2021\ES2021Test
{
    protected $parser = "ES2022";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019", "ES2020", "ES2021", "ES2022");
    }

    protected function getExcludedTests()
    {
        $excluded = parent::getExcludedTests();
        return array_merge(
            $excluded,
            array(
                "AsyncAwait/InvalidAsync3.js"
            )
        );
    }
}