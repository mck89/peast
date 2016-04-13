<?php
namespace Peast\Syntax\ES6\Node;

class ExportAllDeclaration extends Node implements ModuleDeclaration
{
    protected $source;
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource(Literal $source)
    {
        $this->source = $source;
        return $this;
    }
    
    public function compile()
    {
        return "export * from" . $this->getSource()->compile() . ";";
    }
}