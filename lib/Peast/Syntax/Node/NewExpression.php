<?php
namespace Peast\Syntax\Node;

class NewExpression extends Node implements Expression
{
    public function compile()
    {
        return "new " . $this->getCallee()->compile() .
               "(" . $this->compileNodeList($this->getArguments()) . ")";
    }
}