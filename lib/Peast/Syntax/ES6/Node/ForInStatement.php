<?php
namespace Peast\Syntax\ES6\Node;

class ForInStatement extends Node implements Statement
{
    protected $left;
    
    protected $right;
    
    protected $body;
    
    public function getLeft()
    {
        return $this->left;
    }
    
    public function setLeft($left)
    {
        $this->assertType($left, array("VariableDeclaration", "Expression"));
        $this->left = $left;
        return $this;
    }
    
    public function getRight()
    {
        return $this->right;
    }
    
    public function setRight(Expression $right)
    {
        $this->right = $right;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(Statement $body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function compile()
    {
        return "for (" . $this->getLeft()->compile() .
               " in " . $this->getRight()->compile() . ") " .
               $this->getBody()->compile();
    }
}