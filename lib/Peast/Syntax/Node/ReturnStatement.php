<?php
namespace Peast\Syntax\Node;

class ReturnStatement extends Statement
{
    protected $argument;
    
    public function getArgument()
    {
        return $this->argument;
    }
    
    public function setArgument($argument)
    {
        $this->assertType($argument, "Expression", true);
        $this->argument = $argument;
        return $this;
    }
    
    public function getSource()
    {
        $source = "return";
        
        if ($argument = $this->getArgument()) {
            $source .= " " . $argument->getSource(); 
        }
        
        $source .= ";";
        
        return $source;
    }
}