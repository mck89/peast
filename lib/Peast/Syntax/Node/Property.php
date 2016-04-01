<?php
namespace Peast\Syntax\Node;

class Property extends Node
{
    const KIND_INIT = "init";
    
    const KIND_GET = "get";
    
    const KIND_SET = "set";
    
    protected $key;
    
    protected $value;
    
    protected $kind = self::KIND_INIT;
    
    protected $method = false;
    
    protected $shorthand = false;
    
    protected $computed = false;
    
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
    
    public function setValue(Expression $value)
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
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function setMethod($method)
    {
        $this->method = (bool) $method;
        return $this;
    }
    
    public function getShorthand()
    {
        return $this->shorthand;
    }
    
    public function setShorthand($shorthand)
    {
        $this->shorthand = (bool) $shorthand;
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
    
    public function compile()
    {
        $ret = array();
        
        $value = $this->getValue();
        $key = $this->getKey();
        $kind = $this->getKind();
        
        if ($kind === self::KIND_GET || $kind === self::KIND_SET) {
            $ret[] = $kind;
        } elseif ($value instanceof FunctionExpression &&
                  $value->getGenerator()) {
            $ret[] = "*";
        }
        
        $compiledKey = $key->compile();
        $compiledValue = $value->compile();
        if ($this->getComputed()) {
            $ret[] = "[" . $compiledKey . "]";
        } else {
            $ret[] = $compiledKey;
        }
        
        if ($this->getMethod()) {
            $ret[] = preg_replace("/^[^\(]+/", "", $compiledValue);
        } elseif (!($value instanceof Identifier) ||
                  !($key instanceof Identifier) ||
                  $compiledKey !== $compiledValue) {
            $ret[] = $this->getShorthand() ? "=" : ":";
            $ret[] = $compiledValue;
        }
        
        return implode(" ", $ret);
    }
}