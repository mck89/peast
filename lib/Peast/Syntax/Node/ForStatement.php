<?php
namespace Peast\Syntax\Node;

class ForStatement extends Statement
{
    protected $init;
    
    protected $test;
    
    protected $update;
    
    protected $body;
    
    public function getInit()
    {
        return $this->init;
    }
    
    public function setInit(VariableDeclaration $init)
    {
        $this->init = $init;
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
    
    public function getUpdate()
    {
        return $this->update;
    }
    
    public function setUpdate(Expression $update)
    {
        $this->update = $update;
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
    
    public function getSource()
    {
        $source = "for (";
        
        if ($init = $this->getInit()) {
            $source .= $init->getSource();
        }
        
        $source .= ";";
        
        if ($test = $this->getTest()) {
            $source .= $test->getSource();
        }
        
        $source .= ";";
        
        if ($update = $this->getUpdate()) {
            $source .= $update->getSource();
        }
        
        $source .= ") " . $this->getBody()->getSource();
        
        return $source;
    }
}