<?php
namespace Peast\Syntax\Node;

class ExpressionStatement extends Statement
{
    protected $expression;
    
    public function getExpression()
    {
        return $this->expression;
    }
    
    public function setExpression(Expression $expression)
    {
        $this->expression = $expression;
        return $this;
    }
    
    public function compile()
    {
        return $this->getExpression()->compile() . ";";
    }
}