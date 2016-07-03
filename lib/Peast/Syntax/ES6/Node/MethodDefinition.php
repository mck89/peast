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

class MethodDefinition extends Node
{
    const KIND_CONSTRUCTOR = "constructor";
    
    const KIND_METHOD = "method";
    
    const KIND_GET = "get";
    
    const KIND_SET = "set";
    
    protected $key;
    
    protected $value;
    
    protected $kind = self::KIND_METHOD;
    
    protected $computed = false;
    
    protected $static = false;
    
    public function getKey()
    {
        return $this->key;
    }
    
    public function setKey(Expression $key)
    {
        $this->key = $key;
        return $this;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue(FunctionExpression $value)
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
    
    public function getComputed()
    {
        return $this->computed;
    }
    
    public function setComputed($computed)
    {
        $this->computed = (bool) $computed;
        return $this;
    }
    
    public function getStatic()
    {
        return $this->{"static"};
    }
    
    public function setStatic($static)
    {
        $this->{"static"} = (bool) $static;
        return $this;
    }
}