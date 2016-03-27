<?php
namespace Peast\Syntax\Node;

class BlockStatement extends Statement
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
    
    public function compile()
    {
        return "{" . $this->compileNodeList($this->getBody()) . "}";
    }
}