<?php
namespace Peast\Syntax\ES6\Node;

class MemberExpression extends Node implements Expression, Pattern
{
    protected $object;
    
    protected $property;
    
    protected $computed = false;
    
    public function getObject()
    {
        return $this->object;
    }
    
    public function setObject($object)
    {
        $this->assertType($object, array("Expression", "Super"));
        $this->object = $object;
        return $this;
    }
    
    public function getProperty()
    {
        return $this->property;
    }
    
    public function setProperty(Expression $property)
    {
        $this->property = $property;
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
        $object = $this->getObject()->compile();
        $property = $this->getProperty()->compile();
        
        if ($this->getComputed()) {
            return $object . "[" . $property . "]";
        } else {
            return $object . "." . $property;
        }
    }
}