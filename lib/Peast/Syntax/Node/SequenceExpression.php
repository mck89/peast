<?php
namespace Peast\Syntax\Node;

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
    
    public function compile()
    {
        return $this->compileNodeList($this->getExpressions(), ",");
    }
}