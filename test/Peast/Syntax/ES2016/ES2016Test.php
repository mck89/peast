<?php
namespace Peast\test\Syntax\ES2016;

class ES2016Test extends \Peast\test\Syntax\ES2015\ES2015Test
{
    protected $parser = "ES2016";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016");
    }
}