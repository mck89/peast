<?php
namespace Peast\Syntax\ES6\Node;

class MetaProperty extends Node implements Expression
{
    protected $meta;
    
    protected $property;
    
    public function getMeta()
    {
        return $this->meta;
    }
    
    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }
    
    public function getProperty()
    {
        return $this->property;
    }
    
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }
    
    public function compile()
    {
        return $this->meta . "." . $this->property;
    }
}