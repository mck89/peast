<?php
namespace Peast\Syntax\ES6\Node;

use Peast\Syntax\Utils;

class TemplateElement extends Node
{
    protected $value;
    
    protected $tail = false;
    
    protected $rawValue;
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
        $this->rawValue = Utils::quoteLiteralString($value, "`");
        return $this;
    }
    
    public function getTail()
    {
        return $this->tail;
    }
    
    public function setTail($tail)
    {
        $this->tail = (bool) $tail;
        return $this;
    }
    
    public function getRawValue()
    {
        return $this->rawValue;
    }
    
    public function setRawValue($rawValue)
    {
        $rawValue = Utils::unquoteLiteralString($rawValue);
        $this->setValue($rawValue);
        $this->rawValue = $rawValue;
        return $this;
    }
    
    public function compile()
    {
        return $this->getRawValue();
    }
}