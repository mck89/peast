<?php
namespace Peast\Syntax\ES6\Node;

use Peast\Syntax\ES6\Parser;

class Literal extends Node implements Expression
{
    const KIND_NULL = "null";
    
    const KIND_BOOLEAN = "boolean";
    
    const KIND_DOUBLE_QUOTE_STRING = "dq-string";
    
    const KIND_SINGLE_QUOTE_STRING = "sq-string";
    
    const KIND_NUMBER = "number";
    
    protected $value;
    
    protected $kind;
    
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
        $value = $this->getValue();
        $kind = $this->getKind();
        if ($kind === self::KIND_SINGLE_QUOTE_STRING ||
            $kind === self::KIND_DOUBLE_QUOTE_STRING) {
            $quote = $kind === self::KIND_SINGLE_QUOTE_STRING ? "'" : '"';
            $value = Parser::quoteLiteralString($value, $quote);
        }
        return $value;
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
            $this->setValue(Parser::unquoteLiteralString($rawValue));
            $this->setKind($rawValue[0] === "'" ?
                           self::KIND_SINGLE_QUOTE_STRING :
                           self::KIND_DOUBLE_QUOTE_STRING);
        } else {
            $this->setValue($rawValue);
            $this->setKind(self::KIND_NUMBER);
        }
        return $this;
    }
    
    public function compile()
    {
        return $this->getRawValue();
    }
}