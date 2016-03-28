<?php
namespace Peast\Syntax\Node;

class ForStatement extends Node implements Statement
{
    protected $init;
    
    protected $test;
    
    protected $update;
    
    protected $body;
    
    public function getInit()
    {
        return $this->init;
    }
    
    public function setInit($init)
    {
        $this->assertType(
            $init,
            array("VariableDeclaration", "Expression"),
            true
        );
        $this->init = $init;
        return $this;
    }
    
    public function getTest()
    {
        return $this->test;
    }
    
    public function setTest($test)
    {
        $this->assertType($test, "Expression", true);
        $this->test = $test;
        return $this;
    }
    
    public function getUpdate()
    {
        return $this->update;
    }
    
    public function setUpdate($update)
    {
        $this->assertType($update, "Expression", true);
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
    
    public function compile()
    {
        $source = "for (";
        
        if ($init = $this->getInit()) {
            $source .= $init->compile();
        }
        
        $source .= ";";
        
        if ($test = $this->getTest()) {
            $source .= $test->compile();
        }
        
        $source .= ";";
        
        if ($update = $this->getUpdate()) {
            $source .= $update->compile();
        }
        
        $source .= ") " . $this->getBody()->compile();
        
        return $source;
    }
}