<?php
namespace Peast\Syntax\Node;

class VariableDeclaration extends Declaration
{
    const KIND_VAR = "var";
    
    const KIND_LET = "let";
    
    const KIND_CONST = "const";
    
    protected $declarations = array();
    
    protected $kind = self::KIND_VAR;
    
    public function getDeclaration()
    {
        return $this->declarations;
    }
    
    public function setBody($declarations)
    {
        $this->assertArrayOf($declarations, "VariableDeclarator");
        $this->declarations = $declarations;
        return $this;
    }
    
    public function getKind()
    {
        return $this->kind;
    }
    
    public function setKind($kind)
    {
        $this->kind = $kind;
        return $this;
    }
    
    public function getSource()
    {
        return $this->getKind() .
               $this->nodeListToSource($this->getBody(), ",") .
               ";";
    }
}