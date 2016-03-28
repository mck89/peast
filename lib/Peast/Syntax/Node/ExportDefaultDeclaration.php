<?php
namespace Peast\Syntax\Node;

class ExportDefaultDeclaration extends Node implements ModuleDeclaration
{
    protected $declaration;
    
    public function getDeclaration()
    {
        return $this->declaration;
    }
    
    public function setDeclaration($declaration)
    {
        $this->assertType($declaration, array("Declaration", "Expression"));
        $this->declaration = $declaration;
        return $this;
    }
    
    public function compile()
    {
        return "export default " . $this->getDeclaration()->compile() . ";";
    }
}