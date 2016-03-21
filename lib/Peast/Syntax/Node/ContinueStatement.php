<?php
namespace Peast\Syntax\Node;

class ContinueStatement extends Statement
{
    protected $label;
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setLabel(Identifier $label)
    {
        $this->label = $label;
        return $this;
    }
    
    public function getSource()
    {
        $source = "continue";
        
        if ($label = $this->getLabel()) {
            $source .= " " . $label->getSource(); 
        }
        
        $source .= ";";
        
        return $source;
    }
}