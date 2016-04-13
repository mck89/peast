<?php
namespace Peast\Syntax\ES6\Node;

class Literal extends Node implements Expression
{
    const KIND_NULL = "null";
    
    const KIND_BOOLEAN = "boolean";
    
    const KIND_STRING = "string";
    
    const KIND_NUMBER = "number";
    
    protected $value;
    
    protected $kind;
    
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
    
    public function getKind()
    {
        return $this->kind;
    }
    
    public function setKind($kind)
    {
        $this->kind = $kind;
        return $this;
    }
    
    public function getRawValue()
    {
        return $this->rawValue;
    }
    
    public function setRawValue($rawValue)
    {
        if ($rawValue === "null") {
            $this->setValue(null);
            $this->setKind(self::KIND_NULL);
        } elseif ($rawValue === "true" || $rawValue === "false") {
            $this->setValue($rawValue === "true");
            $this->setKind(self::KIND_BOOLEAN);
        } elseif (isset($rawValue[0]) &&
                 ($rawValue[0] === "'" || $rawValue[0] === '"')) {
            $this->setValue(substr($rawValue, 1, strlen($rawValue) - 2));
            $this->setKind(self::KIND_STRING);
        } else {
            $this->setValue($rawValue);
            $this->setKind(self::KIND_NUMBER);
        }
        $this->rawValue = $rawValue;
        return $this;
    }
    
    public function compile()
    {
        return $this->getRawValue();
    }
}