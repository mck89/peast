<?php
namespace Peast\Syntax\ES6\Node;

class AssignmentProperty extends Property
{
    public function getType()
    {
        return "Property";
    }
    
    public function setValue($value)
    {
        $this->assertType($value, "Pattern");
        $this->value = $value;
        return $this;
    }
    
    /**
     * @codeCoverageIgnore
     */
    public function setKind($kind)
    {
        return $this;
    }
    
    /**
     * @codeCoverageIgnore
     */
    public function setMethod($method)
    {
        return $this;
    }
}