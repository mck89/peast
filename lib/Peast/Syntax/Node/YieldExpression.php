<?php
namespace Peast\Syntax\Node;

class YieldExpression extends Node implements Expression
{
    protected $argument;
    
    protected $delegate = false;
    
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
    
    public function getDelegate()
    {
        return $this->delegate;
    }
    
    public function setDelegate($delegate)
    {
        $this->delegate = (bool) $delegate;
        return $this;
    }
    
    public function compile()
    {
        $source = "yield";
        
        if ($this->getDelegate()) {
            $source .= " *";
        }
        
        if ($argument = $this->getArgument()) {
            $source .= " " . $argument->compile();
        }
        
        return $source;
    }
}