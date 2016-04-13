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
        $parts = explode("\\", get_class($this));
        array_pop($parts);
        $nodeClass = implode("\\", $parts) . "\\Node\\$nodeType";
        $node = new $nodeClass;
        return $node->setStartPosition($scanner->getPosition());
    }
    
    public function completeNode(Node $node)
    {
        return $node->setEndPosition($scanner->getPosition());
    }
    
    protected function charSeparatedListOf($fn, $args, $char = ",")
    {
        $multi = is_array($char);
        $list = array();
        $position = $this->scanner->getPosition();
        $valid = true;
        $matchedChar = null;
        while ($param = call_user_func_array(array($this, $fn), $args)) {
            $list[] = $multi ? $param : array($param, $matchedChar);
            $valid = true;
            $matchedChar = $multi ?
                           $this->scanner->consumeOneOf($char) :
                           $this->scanner->consume($char);
            if (!$matchedChar) {
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
        $multi = is_array($operator);
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
                    $node->setLeft($lastNode ?
                                   $lastNode :
                                   ($multi ? $list[0][0] : $list[0]));
                    $node->setOperator($multi ? $expr[1] : $operator);
                    $node->setRight($multi ? $expr[0] : $multi[1]);
                    $lastNode = $this->completeNode($node);
                }
            }
        }
        
        return $lastNode;
    }
}