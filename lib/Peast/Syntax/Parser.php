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
    
    protected function charSeparatedListOf($fn, $args, $char = ",")
    {
        $list = array();
        $position = $this->scanner->getPosition();
        $valid = true;
        while ($param = call_user_func_array(array($this, $fn), $args)) {
            $list[] = $param;
            $valid = true;
            if (!$this->scanner->consume($char)) {
                break;
            } else {
                $valid = false;
            }
        }
        if (!$valid) {
            $this->scanner->setPosition($position);
            return null;
        }
        return $list;
    }
    
    protected function recursiveExpression($fn, $args, $operator, $class)
    {
        $list = $this->charSeparatedListOf($fn, $args, $operator);
        
        if ($list === null) {
            return null;
        } elseif (count($list) === 1) {
            return $list[0];
        } else {
            $lastNode = null;
            foreach ($list as $i => $expr) {
                if ($i) {
                    $node = $this->createNode($class);
                    $node->setLeft($lastNode ? $lastNode : $list[0]);
                    $node->setOperator($operator);
                    $node->setRight($expr);
                    $lastNode = $this->completeNode($node);
                }
            }
        }
        
        return $lastNode;
    }
}