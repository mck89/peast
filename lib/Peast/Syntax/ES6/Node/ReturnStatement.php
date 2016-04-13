<?php
namespace Peast\Syntax\ES6\Node;

class ReturnStatement extends Node implements Statement
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
    
    public function compile()
    {
        $source = "return";
        
        if ($argument = $this->getArgument()) {
            $source .= " " . $argument->compile(); 
        }
        
        $source .= ";";
        
        return $source;
    }
}