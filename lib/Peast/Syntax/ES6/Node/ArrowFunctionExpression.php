<?php
namespace Peast\Syntax\ES6\Node;

class ArrowFunctionExpression extends Node implements Expression, Function_
{
    use Extension\Function_;
    
    protected $expression = false;
    
    public function setBody($body)
    {
        $this->assertType($body, array("BlockStatement", "Expression"));
        $this->body = $body;
        return $this;
    }
    
    public function getExpression()
    {
        return $this->expression;
    }
    
    public function setExpression($expression)
    {
        $this->expression = (bool) $expression;
        return $this;
    }
}