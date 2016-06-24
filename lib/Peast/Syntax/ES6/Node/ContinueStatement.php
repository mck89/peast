<?php
namespace Peast\Syntax\ES6\Node;

class ContinueStatement extends Node implements Statement
{
    protected $label;
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setLabel($label)
    {
        $this->assertType($label, "Identifier", true);
        $this->label = $label;
        return $this;
    }
}