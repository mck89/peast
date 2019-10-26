<?php
namespace Peast\test\Syntax\ES2020;

class ES2020Test extends \Peast\test\Syntax\ES2019\ES2019Test
{
    protected $parser = "ES2020";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019", "ES2020");
    }
}