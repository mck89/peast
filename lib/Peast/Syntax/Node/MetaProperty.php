<?php
namespace Peast\Syntax\Node;

class MetaProperty extends Node implements Expression
{
    protected $meta;
    
    protected $property;
    
    public function getMeta()
    {
        return $this->meta;
    }
    
    public function setMeta(Identifier $meta)
    {
        $this->meta = $meta;
        return $this;
    }
    
    public function getProperty()
    {
        return $this->property;
    }
    
    public function setProperty(Identifier $property)
    {
        $this->property = $property;
        return $this;
    }
    
    public function compile()
    {
        return $this->getMeta()->compile() . "." .
               $this->getProperty()->compile();
    }
}