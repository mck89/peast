<?php
namespace Peast\Syntax\ES6\Node;

class BlockStatement extends Node implements Statement
{
    protected $body = array();
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody($body)
    {
        $this->assertArrayOf($body, "Statement");
        $this->body = $body;
        return $this;
    }
}