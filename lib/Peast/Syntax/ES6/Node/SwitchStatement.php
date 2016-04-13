<?php
namespace Peast\Syntax\ES6\Node;

class SwitchStatement extends Node implements Statement
{
    protected $discriminant;
    
    protected $cases = array();
    
    public function getDiscriminant()
    {
        return $this->discriminant;
    }
    
    public function setDiscriminant(Expression $discriminant)
    {
        $this->discriminant = $discriminant;
        return $this;
    }
    
    public function getCases()
    {
        return $this->cases;
    }
    
    public function setCases($cases)
    {
        $this->assertArrayOf($body, "SwitchCase");
        $this->cases = $cases;
        return $this;
    }
    
    public function compile()
    {
        return "switch (" . $this->getDiscriminant()->compile() . ") {" .
               $this->compileNodeList($this->getBody()) .
               "}";
    }
}