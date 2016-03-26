<?php
namespace Peast\Syntax\Node;

trait Function_
{
    protected $id;
    
    protected $params = array();
    
    protected $body;
    
    protected $generator = false;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->assertType($id, "Identifier", true);
        $this->id = $id;
        return $this;
    }
    
    public function getParams()
    {
        return $this->params;
    }
    
    public function setParams($params)
    {
        $this->assertArrayOf($body, "Pattern");
        $this->params = $params;
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
    
    public function getGenerator()
    {
        return $this->generator;
    }
    
    public function setGenerator($generator)
    {
        $this->generator = (bool) $generator;
        return $this;
    }
    
    public function getSource()
    {
        $source = "function";
        
        if ($this->getGenerator()) {
            $source .= " *";
        }
        
        if ($id = $this->getId()) {
            $source .= " " . $id->getSource();
        }
        
        $source .= " (" . $this->nodeListToSource($this->getParams()) . ")";
        $source .= " {" . $this->getBody()->getSource() . "}";
        
        return $source;
    }
}