<?php
namespace Peast\test\Syntax\ES2021;

class ES2021Test extends \Peast\test\Syntax\ES2020\ES2020Test
{
    protected $parser = "ES2021";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019", "ES2020", "ES2021");
    }
}