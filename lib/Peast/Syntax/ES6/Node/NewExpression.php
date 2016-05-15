<?php
namespace Peast\Syntax\ES6\Node;

class NewExpression extends CallExpression
{
    public function compile()
    {
        return "new " . $this->getCallee()->compile() .
               "(" . $this->compileNodeList($this->getArguments()) . ")";
    }
}