<?php
namespace Peast\Syntax\ES6\Node;

class SwitchCase extends Node
{
    protected $test;
    
    protected $consequent = array();
    
    public function getTest()
    {
        return $this->test;
    }
    
    public function setTest($test)
    {
        $this->assertType($test, "Expression", true);
        $this->test = $test;
        return $this;
    }
    
    public function getConsequent()
    {
        return $this->consequent;
    }
    
    public function setConsequent($consequent)
    {
        $this->assertArrayOf($body, "Statement");
        $this->consequent = $consequent;
        return $this;
    }
    
    public function compile()
    {
        if ($test = $this->getTest()) {
            $source = "case " . $test->compile();
        } else {
            $source = "default";
        }
        $source .= ": " . $this->compileNodeList($this->getConsequent());
        return $source;
    }
}