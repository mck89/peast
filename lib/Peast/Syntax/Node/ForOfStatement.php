<?php
namespace Peast\Syntax\Node;

class ForOfStatement extends ForInStatement
{
    public function getSource()
    {
        return "for (" . $this->getLeft()->getSource() .
               " of " . $this->getRight()->getSource() . ") " .
               $this->getBody()->getSource();
    }
}