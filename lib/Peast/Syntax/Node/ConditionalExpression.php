<?php
namespace Peast\Syntax\Node;

class ConditionalExpression extends Node implements Expression
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
    
    public function setConsequent(Expression $consequent)
    {
        $this->consequent = $consequent;
        return $this;
    }
    
    public function getAlternate()
    {
        return $this->alternate;
    }
    
    public function setAlternate(Expression $alternate)
    {
        $this->alternate = $alternate;
        return $this;
    }
    
    public function compile()
    {
        return $this->getTest()->compile() . "?" .
               $this->getConsequent()->compile() . ":" .
               $this->getAlternate()->compile();
    }
}