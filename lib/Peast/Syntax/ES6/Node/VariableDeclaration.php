<?php
namespace Peast\Syntax\ES6\Node;

class VariableDeclaration extends Node implements Declaration
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
    
    public function compile()
    {
        return $this->getKind() .
               $this->compileNodeList($this->getBody(), ",") .
               ";";
    }
}