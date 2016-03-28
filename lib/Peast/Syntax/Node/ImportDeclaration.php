<?php
namespace Peast\Syntax\Node;

class ImportDeclaration extends Node implements ModuleDeclaration
{
    protected $specifiers = array();
    
    protected $source;
    
    public function getSpecifiers()
    {
        return $this->specifiers;
    }
    
    public function setSpecifiers($specifiers)
    {
        $this->assertArrayOf($body, array(
            "ImportSpecifier",
            "ImportDefaultSpecifier",
            "ImportNamespaceSpecifier"
        ));
        $this->specifiers = $specifiers;
        return $this;
    }
    
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
        $specifiers = $this->getSpecifiers();
        
        if (!count($specifiers)) {
            return "import " . $this->getSource()->compile();
        }
        
        $grouped = array();
        $strings = array();
        foreach ($specifiers as $spec) {
            $type = $spec->getType();
            if (!isset($grouped[$type])) {
                $grouped[$type] = array();
            }
            $grouped[$type][] = $spec;
        }
        
        if (isset($grouped["ImportDefaultSpecifier"])) {
            $strings[] = $this->compileNodeList(
                $grouped["ImportDefaultSpecifier"],
                ", "
            );
        }
        
        if (isset($grouped["ImportNamespaceSpecifier"])) {
            $strings[] = $this->compileNodeList(
                $grouped["ImportNamespaceSpecifier"],
                ", "
            );
        }
        
        if (isset($grouped["ImportSpecifier"])) {
            $strings[] = "{" . $this->compileNodeList(
                $grouped["ImportSpecifier"],
                ", "
            ) . "}";
        }
        
        return "import " . implode(",", $strings) .
               " from " . $this->getSource()->compile();
    }
}