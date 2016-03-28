<?php
namespace Peast\Syntax\Node;

class WithStatement extends Node implements Statement
{
    protected $object;
    
    protected $body;
    
    public function getObject()
    {
        return $this->object;
    }
    
    public function setObject(Expression $object)
    {
        $this->object = $object;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(Statement $body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function compile()
    {
        return "with (" . $this->getObject()->compile() . ") " .
               $this->getBody()->compile();
    }
}