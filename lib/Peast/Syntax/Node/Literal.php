<?php
namespace Peast\Syntax\Node;

class Literal extends Node implements Expression
{
    protected $value;
    
    protected $rawValue;
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    public function getRawValue()
    {
        return $this->rawValue;
    }
    
    public function setRawValue($rawValue)
    {
        $this->rawValue = $rawValue;
        return $this;
    }
    
    public function compile()
    {
        return $this->getRawValue();
    }
}