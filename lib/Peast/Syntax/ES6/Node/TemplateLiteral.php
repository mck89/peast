<?php
namespace Peast\Syntax\ES6\Node;

class TemplateLiteral extends Node implements Expression
{
    protected $quasis = array();
    
    protected $expressions = array();
    
    public function getQuasis()
    {
        return $this->quasis;
    }
    
    public function setQuasis($quasis)
    {
        $this->assertArrayOf($quasis, "TemplateElement");
        $this->quasis = $quasis;
        return $this;
    }
    
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