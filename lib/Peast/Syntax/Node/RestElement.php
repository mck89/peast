<?php
namespace Peast\Syntax\Node;

class RestElement extends Pattern
{
    protected $argument;
    
    public function getArgument()
    {
        return $this->argument;
    }
    
    public function setArgument(Pattern $argument)
    {
        $this->argument = $argument;
        return $this;
    }
    
    public function compile()
    {
        return "..." . $this->getArgument()->compile();
    }
}