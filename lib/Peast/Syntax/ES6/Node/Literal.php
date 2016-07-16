<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES6\Node;

use Peast\Syntax\Utils;

/**
 * A node that represents a literal, such as strings, numbers, booleans or null.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Literal extends Node implements Expression
{
    //Kind constants
    /**
     * Null literal
     */
    const KIND_NULL = "null";
    
    /**
     * Boolean literal
     */
    const KIND_BOOLEAN = "boolean";
    
    /**
     * Double quoted string literal
     */
    const KIND_DOUBLE_QUOTE_STRING = "dq-string";
    
    /**
     * Single quoted string literal
     */
    const KIND_SINGLE_QUOTE_STRING = "sq-string";
    
    /**
     * Decimal number literal
     */
    const KIND_DECIMAL_NUMBER = "decimal";
    
    /**
     * Hexadecimal number literal
     */
    const KIND_HEXADECIMAL_NUMBER = "hexadecimal";
    
    /**
     * Octal number literal
     */
    const KIND_OCTAL_NUMBER = "octal";
    
    /**
     * Binary number literal
     */
    const KIND_BINARY_NUMBER = "binary";
    
    /**
     * Node's value
     * 
     * @var mixed
     */
    protected $value;
    
    /**
     * Node's kind that is one of the kind constants
     * 
     * @var string
     */
    protected $kind;
    
    /**
     * Node's raw value
     * 
     * @var string
     */
    protected $raw;
    
    /**
     * Returns node's value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Sets node's value
     * 
     * @param mixed $value Value
     * 
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        
        $kind = $this->getKind();
        if ($kind === self::KIND_SINGLE_QUOTE_STRING ||
            $kind === self::KIND_DOUBLE_QUOTE_STRING
        ) {
            $quote = $kind === self::KIND_SINGLE_QUOTE_STRING ? "'" : '"';
            $raw = Utils::quoteLiteralString($value, $quote);
        } elseif ($kind === self::KIND_NULL) {
            $raw = "null";
        } elseif ($kind === self::KIND_BOOLEAN) {
            $raw = $value ? "true" : "false";
        } elseif ($kind === self::KIND_HEXADECIMAL_NUMBER) {
            $raw = "0x" . dechex($value);
        } elseif ($kind === self::KIND_BINARY_NUMBER) {
            $raw = "0b" . decbin($value);
        } elseif ($kind === self::KIND_OCTAL_NUMBER) {
            $raw = "0o" . decoct($value);
        } else {
            $raw = "$value";
        }
        
        $this->raw = $raw;
        
        return $this;
    }
    
    /**
     * Returns node's kind
     * 
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }
    
    /**
     * Sets node's kind
     * 
     * @param string $kind Kind
     * 
     * @return $this
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
        return $this;
    }
    
    /**
     * Return node's raw value
     * 
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }
    
    /**
     * Sets node's raw value, for exaple for strings it's the value wrapped in
     * quotes.
     * 
     * @param string $rawValue Raw value
     * 
     * @return $this
     */
    public function setRaw($rawValue)
    {
        if ($rawValue === "null") {
            $this->setValue(null);
            $this->setKind(self::KIND_NULL);
        } elseif ($rawValue === "true" || $rawValue === "false") {
            $this->setValue($rawValue === "true");
            $this->setKind(self::KIND_BOOLEAN);
        } elseif (isset($rawValue[0]) &&
            ($rawValue[0] === "'" || $rawValue[0] === '"')
        ) {
            $this->setValue(Utils::unquoteLiteralString($rawValue));
            $this->setKind(
                $rawValue[0] === "'" ?
                self::KIND_SINGLE_QUOTE_STRING :
                self::KIND_DOUBLE_QUOTE_STRING
            );
        } else {
            $kind = self::KIND_DECIMAL_NUMBER;
            $value = $rawValue;
            if ($value[0] === "0" && isset($value[1])) {
                $secondChar = strtolower($value[1]);
                if ($secondChar === "b") {
                    $kind = self::KIND_BINARY_NUMBER;
                    $value = bindec($value);
                } elseif ($secondChar === "x") {
                    $kind = self::KIND_HEXADECIMAL_NUMBER;
                    $value = hexdec($value);
                } elseif ($secondChar === "o" ||
                    preg_match("/^0[0-7]+$/", $value)
                ) {
                    $kind = self::KIND_OCTAL_NUMBER;
                    $value = octdec($value);
                }
            }
            $value = (float) $value;
            $value = strpos("$value", ".") === false ||
                     preg_match("/\.0*$/", "$value") ?
                     (int) $value :
                     (float) $value;
            $this->setKind($kind);
            $this->setValue($value);
        }
        $this->raw = $rawValue;
        return $this;
    }
}