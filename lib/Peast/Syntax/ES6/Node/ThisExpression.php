<?php
namespace Peast\Syntax\ES6\Node;

class ThisExpression extends Node implements Expression
{
    public function compile()
    {
        return "this";
    }
}