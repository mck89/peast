<?php
namespace Peast\Syntax\ES6\Node;

class AssignmentProperty extends Property
{
    public function getType()
    {
        return "Property";
    }
    
    public function setValue(Pattern $value)
    {
        $this->value = $value;
        return $this;
    }
    
    public function setKind($kind)
    {
        return $this;
    }
    
    public function setMethod($method)
    {
        return $this;
    }
}