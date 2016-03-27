<?php
namespace Peast\Syntax\Node;

class CatchClause extends Node
{
    protected $param;
    
    protected $body;
    
    public function getParam()
    {
        return $this->param;
    }
    
    public function setParam(Pattern $param)
    {
        $this->param = $param;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(BlockStatement $body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function compile()
    {
        $source = "catch(" . $this->getParam()->getSource . ")" .
                  $this->getBody()->compile();
    }
}