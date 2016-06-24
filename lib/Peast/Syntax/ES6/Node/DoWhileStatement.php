<?php
namespace Peast\Syntax\ES6\Node;

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
}