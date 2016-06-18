<?php
namespace Peast\Syntax;

abstract class Parser
{
    protected $scanner;
    
    abstract public function parse();
    
    abstract public function setSource($source, $encoding = null);
    
    public function createNode($nodeType, $position)
    {
        $parts = explode("\\", get_class($this));
        array_pop($parts);
        $nodeClass = implode("\\", $parts) . "\\Node\\$nodeType";
        $node = new $nodeClass;
        if ($position instanceof Node || $position instanceof Token) {
            $position = $position->getLocation()->getStart();
        } elseif (is_array($position)) {
            if (count($position)) {
                $position = $position[0]->getLocation()->getStart();
            } else {
                $position = $this->scanner->getPosition();
            }
        }
        return $node->setStartPosition($position);
    }
    
    public function completeNode(Node $node, $position = null)
    {
        return $node->setEndPosition(
            $position ? $position : $this->scanner->getPosition()
        );
    }
    
    public function error($message = "", $position = null)
    {
        if (!$position) {
            $position = $this->scanner->getPosition();
        }
        if (!$message) {
            $token = $this->scanner->getToken();
            if ($token === null) {
                $message = "Unexpected end of input";
            } else {
                $message = "Unexpected: " . $token->getValue();
            }
        }
        throw new Exception($message, $position);
    }
    
    protected function assertEndOfStatement()
    {
        //The end of statement is reached when it is followed by line
        //terminators, end of source, "}" or ";". In the last case the token
        //must be consumed
        if (!$this->scanner->noLineTerminators()) {
            return true;
        } else {
            if ($this->scanner->consume(";")) {
                return true;
            }
            $token = $this->getToken();
            if (!$token || $token->getValue() === "}") {
                return true;
            }
        }
        return $this->error();
    }
    
    protected function charSeparatedListOf($fn, $args = array(), $char = ",")
    {
        $list = array();
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
            $this->error();
            return null;
        }
        return $list;
    }
}