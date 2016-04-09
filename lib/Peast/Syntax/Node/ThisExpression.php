<?php
namespace Peast\Syntax\Node;

class ThisExpression extends Node implements Expression
{
    public function compile()
    {
        return "this";
    }
}