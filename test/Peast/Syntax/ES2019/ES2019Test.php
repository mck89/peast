<?php
namespace Peast\test\Syntax\ES2019;

class ES2019Test extends \Peast\test\Syntax\ES2018\ES2018Test
{
    protected $parser = "ES2019";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019");
    }
}