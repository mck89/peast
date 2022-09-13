<?php
namespace Peast\test\Syntax\ES2023;

class ES2023Test extends \Peast\test\Syntax\ES2022\ES2022Test
{
    protected $parser = "ES2023";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019", "ES2020", "ES2021", "ES2022", "ES2023");
    }
}