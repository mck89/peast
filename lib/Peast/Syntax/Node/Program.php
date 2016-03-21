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
        if ($this->getSourceType() === self::SOURCE_TYPE_SCRIPT) {
            $this->assertArrayOf($body, "Statement");
        } else {
            $this->assertArrayOf($body, "ModuleDeclaration");
        }
        $this->body = $body;
        return $this;
    }
    
    public function getSource()
    {
        return $this->nodeListToSource($this->getBody());
    }
}