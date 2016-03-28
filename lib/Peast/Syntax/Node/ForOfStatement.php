<?php
namespace Peast\Syntax\Node;

class ForOfStatement extends Node implements ForInStatement
{
    public function compile()
    {
        return "for (" . $this->getLeft()->compile() .
               " of " . $this->getRight()->compile() . ") " .
               $this->getBody()->compile();
    }
}