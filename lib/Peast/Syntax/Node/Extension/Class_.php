<?php
namespace Peast\Syntax\Node\Extension;

trait Class_
{
    protected $id;
    
    protected $superClass;
    
    protected $body;
    
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
    
    public function getSuperClass()
    {
        return $this->superClass;
    }
    
    public function setSuperClass($superClass)
    {
        $this->assertType($superClass, "Expression", true);
        $this->superClass = $superClass;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(ClassBody $body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function compile()
    {
        $source = "class";
        
        if ($id = $this->getId()) {
            $source .= " " . $id->compile();
        }
        
        if ($superClass = $this->getSuperClass()) {
            $source .= " extends " . $superClass->compile();
        }
        
        $source .= " {" . $this->getBody()->compile() . "}";
        
        return $source;
    }
}