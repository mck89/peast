<?php
namespace Peast\Syntax\Node;

class DoWhileStatement extends Statement
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
        return "do" . $this->getBody()->getSource .
               "while (" . $this->getTest()->compile() . ")";
    }
}