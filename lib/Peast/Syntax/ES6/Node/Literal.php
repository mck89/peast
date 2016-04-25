<?php
namespace Peast\Syntax\ES6\Node;

use Peast\Syntax\ES6\Parser;

class Literal extends Node implements Expression
{
    const KIND_NULL = "null";
    
    const KIND_BOOLEAN = "boolean";
    
    const KIND_DOUBLE_QUOTE_STRING = "dq-string";
    
    const KIND_SINGLE_QUOTE_STRING = "sq-string";
    
    const KIND_DECIMAL_NUMBER = "decimal";
    
    const KIND_HEXADECIMAL_NUMBER = "hexadecimal";
    
    const KIND_OCTAL_NUMBER = "octal";
    
    const KIND_BINARY_NUMBER = "binary";
    
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
        } elseif ($kind === self::KIND_NULL) {
            $value = "null";
        } elseif ($kind === self::KIND_BOOLEAN) {
            $value = $value ? "true" : "false";
        } elseif ($kind === self::KIND_HEXADECIMAL_NUMBER) {
            $value = "0x" . dechex($value);
        } elseif ($kind === self::KIND_BINARY_NUMBER) {
            $value = "0b" . decbin($value);
        } elseif ($kind === self::KIND_OCTAL_NUMBER) {
            $value = "0o" . decoct($value);
        } else {
            $value = "$value";
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
            $kind = self::KIND_DECIMAL_NUMBER;
            $value = $rawValue;
            if ($value[0] === "0" && isset($value[1])) {
                $secondChar = strtolower($value[1]);
                $parts = preg_split("/e/i", $value);
                $value = $parts[0];
                if ($secondChar === "b") {
                    $kind = self::KIND_BINARY_NUMBER;
                    $value = bindec($value);
                } elseif ($secondChar === "x") {
                    $kind = self::KIND_HEXADECIMAL_NUMBER;
                    $value = hexdec($value);
                } elseif ($secondChar === "o" ||
                          preg_match("/^0[0-7]+$/", $parts[0])) {
                    $kind = self::KIND_OCTAL_NUMBER;
                    $value = octdec($value);
                }
                if (isset($parts[1])) {
                    $value = $value . "e" . $parts[1];
                }
            }
            $value = strpos("$value", ".") === false ?
                     (int) $value :
                     (float) $value;
            $this->setKind($kind);
            $this->setValue($value);
        }
        return $this;
    }
    
    public function compile()
    {
        return $this->getRawValue();
    }
}