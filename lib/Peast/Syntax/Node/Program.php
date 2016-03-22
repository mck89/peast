<?php
namespace Peast\Syntax\Node;

class Program extends Node
{
    const SOURCE_TYPE_SCRIPT = "script";
    
    const SOURCE_TYPE_MODULE = "module";
    
    protected $sourceType = self::SOURCE_TYPE_SCRIPT;
    
    protected $body = array();
    
    public function getSourceType()
    {
        return $this->sourceType;
    }
    
    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody($body)
    {
        $this->assertArrayOf($body, array("Statement", "ModuleDeclaration");
        $this->body = $body;
        return $this;
    }
    
    public function getSource()
    {
        return $this->nodeListToSource($this->getBody());
    }
}