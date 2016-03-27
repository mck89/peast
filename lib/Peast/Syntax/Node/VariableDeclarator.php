<?php
namespace Peast\Syntax\Node;

class VariableDeclarator extends Node
{
    protected $id;
    
    protected $init;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId(Pattern $id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function getInit()
    {
        return $this->init;
    }
    
    public function setInit($init)
    {
        $this->assertType($init, "Expression", true);
        $this->init = $init;
        return $this;
    }
    
    public function compile()
    {
        $source = $this->getId()->compile() . " = ";
        
        if ($init = $this->getInit()) {
            $source .= $init->compile();
        }
        
        return $source;
    }
}