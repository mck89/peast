<?php
namespace Peast\Syntax;

use Peast\Syntax\Node\Node;

abstract class Parser
{
    protected $scanner;
    
    public function setScanner(Scanner $scanner)
    {
        $this->scanner = $scanner;
        return $this;
    }
    
    abstract public function parse();
    
    public function createNode($nodeType)
    {
        $nodeClass = "Peast\\Syntax\\Node\\$nodeType";
        $node = new $nodeClass;
        return $node->setStartPosition($scanner->getPosition());
    }
    
    public function completeNode(Node $node)
    {
        return $node->setEndPosition($scanner->getPosition());
    }
}