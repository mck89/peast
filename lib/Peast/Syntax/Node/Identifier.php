<?php
namespace Peast\Syntax\Node;

class Identifier extends Node implements Expression, Pattern
{
    protected $name;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function compile()
    {
        return $this->getName();
    }
}