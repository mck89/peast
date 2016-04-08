<?php
namespace Peast\Syntax\Node;

class CallExpression extends Node implements Expression
{
    protected $callee;
    
    protected $arguments = array();
    
    public function getCallee()
    {
        return $this->callee;
    }
    
    public function setCallee($callee)
    {
        $this->assertType($callee, array("Expression", "Super"));
        $this->callee = $callee;
        return $this;
    }
    
    public function getArguments()
    {
        return $this->arguments;
    }
    
    public function setArguments($arguments)
    {
        $this->assertArrayOf($arguments, array("Expression", "SpreadElement"));
        $this->arguments = $arguments;
        return $this;
    }
    
    public function compile()
    {
        return $this->getCallee()->compile() .
               "(" . $this->compileNodeList($this->getArguments()) . ")";
    }
}