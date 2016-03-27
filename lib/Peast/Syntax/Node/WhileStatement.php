<?php
namespace Peast\Syntax\Node;

class WhileStatement extends Statement
{
    protected $test;
    
    protected $body;
    
    public function getTest()
    {
        return $this->test;
    }
    
    public function setTest(Expression $test)
    {
        $this->test = $test;
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
        return "while (" . $this->getTest()->compile() . ")" .
               $this->getBody()->compile();
    }
}