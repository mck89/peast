<?php
namespace Peast\Syntax\ES6\Node;

use Peast\Syntax\ES6\Parser;

class TemplateElement extends Node
{
    protected $value;
    
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
        $value = $this->getValue();
        $value = Parser::quoteLiteralString($value, "`");
        return substr($value, 1, -1);
    }
    
    public function setRawValue($rawValue)
    {
        $this->setValue(Parser::unquoteLiteralString("`$rawValue`"));
        return $this;
    }
    
    public function compile()
    {
        return $this->getRawValue();
    }
}