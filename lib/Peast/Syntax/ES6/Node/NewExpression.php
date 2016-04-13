<?php
namespace Peast\Syntax\ES6\Node;

class NewExpression extends Node implements Expression
{
    public function compile()
    {
        return "new " . $this->getCallee()->compile() .
               "(" . $this->compileNodeList($this->getArguments()) . ")";
    }
}