<?php
namespace Peast\Syntax\Node;

class BreakStatement extends Node implements Statement
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
    
    public function compile()
    {
        $source = "break";
        
        if ($label = $this->getLabel()) {
            $source .= " " . $label->compile(); 
        }
        
        $source .= ";";
        
        return $source;
    }
}