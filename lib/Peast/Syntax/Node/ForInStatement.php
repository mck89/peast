<?php
namespace Peast\Syntax\Node;

class ForInStatement extends Statement
{
    protected $left;
    
    protected $right;
    
    protected $body;
    
    public function getLeft()
    {
        return $this->left;
    }
    
    public function setLeft(VariableDeclaration $left)
    {
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
    
    public function getSource()
    {
        return "for (" . $this->getLeft()->getSource() .
               " in " . $this->getRight()->getSource() . ") " .
               $this->getBody()->getSource();
    }
}