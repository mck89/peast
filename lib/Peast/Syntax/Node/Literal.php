<?php
namespace Peast\Syntax\Node;

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
    
    public function getKind()
    {
        return $this->kind;
    }
    
    public function getRawValue()
    {
        return $this->rawValue;
    }
    
    public function setRawValue($rawValue)
    {
        if ($rawValue === "null") {
            $this->value = null;
            $this->kind = self::KIND_NULL;
        } elseif ($rawValue === "true" || $rawValue === "false") {
            $this->value = $rawValue === "true";
            $this->kind = self::KIND_BOOLEAN;
        } elseif (isset($rawValue[0]) &&
                 ($rawValue[0] === "'" || $rawValue[0] === '"')) {
            $this->value = substr($rawValue, 1, strlen($rawValue) - 2);
            $this->kind = self::KIND_STRING;
        } else {
            $this->value = $rawValue;
            $this->kind = self::KIND_NUMBER;
        }
        $this->rawValue = $rawValue;
        return $this;
    }
    
    public function compile()
    {
        return $this->getRawValue();
    }
}