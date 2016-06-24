<?php
namespace Peast\Syntax\ES6\Node;

class SequenceExpression  extends Node implements Expression
{
    protected $expressions = array();
    
    public function getExpressions()
    {
        return $this->expressions;
    }
    
    public function setExpressions($expressions)
    {
        $this->assertArrayOf($expressions, "Expression");
        $this->expressions = $expressions;
        return $this;
    }
}