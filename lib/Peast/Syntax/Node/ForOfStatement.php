<?php
namespace Peast\Syntax\Node;

class ForOfStatement extends ForInStatement
{
    public function compile()
    {
        return "for (" . $this->getLeft()->compile() .
               " of " . $this->getRight()->compile() . ") " .
               $this->getBody()->compile();
    }
}