<?php
namespace Peast\Syntax\Node;

class DoWhileStatement extends Node implements Statement
{
    protected $body;
    
    protected $test;
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(Statement $body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function getTest()
    {
        return $this->test;
    }
    
    public function setTest(Expression $test)
    {
        $this->test = $test;
        return $this;
    }
    
    public function compile()
    {
        return "do" . $this->getBody()->compile() .
               "while (" . $this->getTest()->compile() . ")";
    }
}