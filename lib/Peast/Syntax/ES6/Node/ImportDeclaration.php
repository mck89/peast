<?php
namespace Peast\Syntax\ES6\Node;

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
        $this->assertArrayOf($specifiers, array(
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
}