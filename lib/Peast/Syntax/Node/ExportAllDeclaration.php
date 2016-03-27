<?php
namespace Peast\Syntax\Node;

class ExportAllDeclaration extends ModuleDeclaration
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