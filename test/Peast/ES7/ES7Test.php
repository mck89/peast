<?php
namespace test\Peast\ES7;

class ES7Test extends \test\Peast\ES6\ES6Test
{
    protected $parser = "ES7";
    
    protected function getTestVersions()
    {
        return array("ES6", "ES7");
    }
}