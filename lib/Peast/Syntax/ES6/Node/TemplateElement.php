<?php
/**
 * This file is part of the REBuilder package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
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
        $rawValue = preg_replace("#^[`}]|(?:`|\\\$\{)$#", "", $rawValue);
        $this->setValue(Utils::unquoteLiteralString("`$rawValue`"));
        $this->rawValue = $rawValue;
        return $this;
    }
}