<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax;

abstract class Parser
{
    protected $scanner;
    
    protected $options;
    
    public function __construct($source, $options = array())
    {
        $this->options = $options;
        
        $encoding = isset($options["sourceEncoding"]) ?
                    $options["sourceEncoding"] :
                    null;
        
        //Create the scanner
        $classParts = explode("\\", get_class($this));
        array_pop($classParts);
        $classParts[] = "Scanner";
        $scannerClasss = implode("\\", $classParts);
        $this->scanner = new $scannerClasss($source, $encoding);
    }
    
    abstract public function parse();
    
    public function tokenize()
    {
        $this->scanner->enableTokenRegistration();
        $this->parse();
        return $this->scanner->getTokens();
    }
    
    /**
     * @codeCoverageIgnore
     */
    protected function createNode($nodeType, $position)
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
    
    /**
     * @codeCoverageIgnore
     */
    protected function completeNode(Node $node, $position = null)
    {
        return $node->setEndPosition(
            $position ? $position : $this->scanner->getPosition()
        );
    }
    
    protected function error($message = "", $position = null)
    {
        if (!$message) {
            $token = $this->scanner->getToken();
            if ($token === null) {
                $message = "Unexpected end of input";
            } else {
                $position = $token->getLocation()->getStart();
                $message = "Unexpected: " . $token->getValue();
            }
        }
        if (!$position) {
            $position = $this->scanner->getPosition();
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
            $token = $this->scanner->getToken();
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