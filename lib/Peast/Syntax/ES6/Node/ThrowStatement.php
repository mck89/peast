<?php
namespace Peast\Syntax\ES6\Node;

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
}