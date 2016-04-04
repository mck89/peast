<?php
namespace Peast\Syntax\Node;

class SpreadElement extends Node
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
        return "..." . $this->getArgument()->compile();
    }
}