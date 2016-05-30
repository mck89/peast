<?php
namespace Peast\Syntax\ES6\Node;

class ExportNamedDeclaration extends Node implements ModuleDeclaration
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
        $this->assertArrayOf($specifiers, "ExportSpecifier");
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
        $ret = "export";
        
        if ($declaration = $this->getDeclaration()) {
            return $ret . " " . $declaration->compile();
        }
        
        $ret .= " {" .
                   $this->compileNodeList($this->getSpecifiers(), ", ") .
                   "}";
        
        if ($source = $this->getSource()) {
            $ret .= " from ". $source->compile();
        }
        
        return $ret . ";";
    }
}