<?php
namespace Peast\Syntax\ES6\Node;

class RestElement extends Node implements Pattern
{
    protected $argument;
    
    public function getArgument()
    {
        return $this->argument;
    }
    
    public function setArgument(Pattern $argument)
    {
        $this->argument = $argument;
        return $this;
    }
}