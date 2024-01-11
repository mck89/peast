<?php
namespace Peast\test\Syntax\ES2024;

class ES2024Test extends \Peast\test\Syntax\ES2023\ES2023Test
{
    protected $parser = "ES2024";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019", "ES2020", "ES2021", "ES2022", "ES2023", "ES2024");
    }
}