<?php
namespace Peast\Syntax\Node;

class IfStatement extends Statement
{
    protected $test;
    
    protected $consequent;
    
    protected $alternate;
    
    public function getTest()
    {
        return $this->test;
    }
    
    public function setTest(Expression $test)
    {
        $this->test = $test;
        return $this;
    }
    
    public function getConsequent()
    {
        return $this->consequent;
    }
    
    public function setConsequent(Statement $consequent)
    {
        $this->consequent = $consequent;
        return $this;
    }
    
    public function getAlternate()
    {
        return $this->alternate;
    }
    
    public function setAlternate($alternate)
    {
        $this->assertType($alternate, "Statement", true);
        $this->alternate = $alternate;
        return $this;
    }
    
    public function getSource()
    {
        $source = "if (" . $this->getTest()->getSource() . ") ";
        $source .= $this->getConsequent()->getSource();
        
        if ($alternate = $this->getAlternate()) {
            $source .= " else " . $alternate->getSource();
        }
        
        return $source;
    }
}