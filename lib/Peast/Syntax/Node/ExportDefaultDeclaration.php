<?php
namespace Peast\Syntax\Node;

class ExportDefaultDeclaration extends ModuleDeclaration
{
    protected $declaration;
    
    public function getDeclaration()
    {
        return $this->declaration;
    }
    
    public function setDeclaration($declaration)
    {
        $this->assertArrayOf($declaration, array("Declaration", "Expression"));
        $this->declaration = $declaration;
        return $this;
    }
    
    public function compile()
    {
        return "export default " . $this->getSource()->getDeclaration() . ";";
    }
}