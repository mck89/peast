<?php
namespace Peast\Syntax\Node;

class ExportNamedDeclaration extends ModuleDeclaration
{
    protected $declaration;
    
    protected $specifiers = array();
    
    protected $source;
    
    public function getDeclaration()
    {
        return $this->declaration;
    }
    
    public function setDeclaration($declaration)
    {
        $this->assertType($declaration, "Declaration", true);
        $this->declaration = $declaration;
        return $this;
    }
    
    public function getSpecifiers()
    {
        return $this->specifiers;
    }
    
    public function setSpecifiers($specifiers)
    {
        $this->assertArrayOf($body, "ExportSpecifier");
        $this->specifiers = $specifiers;
        return $this;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        $this->assertType($source, "Literal", true);
        $this->source = $source;
        return $this;
    }
    
    public function compile()
    {
        $source = "export";
        
        if ($declaration = $this->getDeclaration()) {
            return $source . " " . $declaration->compile();
        }
        
        $source .= " {" .
                   $this->compileNodeList($this->getSpecifiers(), ", ") .
                   "}";
        
        if ($source = $this->getSource()) {
            $source .= " from ". $source->compile();
        }
        
        return $source . ";";
    }
}