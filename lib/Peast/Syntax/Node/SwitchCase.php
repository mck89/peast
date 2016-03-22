<?php
namespace Peast\Syntax\Node;

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
    
    public function getSource()
    {
        if ($test = $this->getTest()) {
            $source = "case " . $test->getSource();
        } else {
            $source = "default";
        }
        $source .= ": " . $this->nodeListToSource($this->getConsequent());
        return $source;
    }
}