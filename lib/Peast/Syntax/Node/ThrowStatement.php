<?php
namespace Peast\Syntax\Node;

class ThrowStatement extends Node implements Statement
{
    protected $argument;
    
    public function getArgument()
    {
        return $this->argument;
    }
    
    public function setArgument(Expression $argument)
    {
        $this->argument = $argument;
        return $this;
    }
    
    public function compile()
    {
        return "throw " . $this->getArgument()->compile() . ";";
    }
}