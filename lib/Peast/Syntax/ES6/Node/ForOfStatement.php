<?php
namespace Peast\Syntax\ES6\Node;

class ForOfStatement extends ForInStatement
{
    public function compile()
    {
        return "for (" . $this->getLeft()->compile() .
               " of " . $this->getRight()->compile() . ") " .
               $this->getBody()->compile();
    }
}