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

/**
 * Base class for parsers.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
abstract class Parser
{
    /**
     * Associated scanner
     * 
     * @var Scanner 
     */
    protected $scanner;
    
    /**
     * Parsing options
     * 
     * @var array
     */
    protected $options;
    
    /**
     * Class constructor
     * 
     * @param string $source  Source code
     * @param array  $options Parsing options
     */
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
    
    /**
     * Parses the source
     * 
     * @abstract
     */
    abstract public function parse();
    
    /**
     * Returns parsed tokens from the source code
     * 
     * @return Token[]
     */
    public function tokenize()
    {
        $this->scanner->enableTokenRegistration();
        $this->parse();
        return $this->scanner->getTokens();
    }
    
    /**
     * Creates a node
     * 
     * @param string $nodeType Node's type
     * @param mixed  $position Node's start position
     * 
     * @return Node
     * 
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
     * Completes a node by adding the end position
     * 
     * @param Node $node     Node to complete
     * @param type $position Node's end position
     * 
     * @return Node
     * 
     * @codeCoverageIgnore
     */
    protected function completeNode(Node $node, $position = null)
    {
        return $node->setEndPosition(
            $position ? $position : $this->scanner->getPosition()
        );
    }
    
    /**
     * Throws a syntax error
     * 
     * @param string $message    Error message
     * @param Position $position Error position
     * 
     * @throws Exception
     */
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
    
    /**
     * Asserts that a valid end of statement follows the current position
     * 
     * @return boolean
     * 
     * @throws Exception
     */
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
    
    /**
     * Parses a character separated list of instructions or null if the
     * sequence is not valid
     * 
     * @param callable $fn   Parsing instruction function
     * @param array    $args Arguments that will be passed to the function
     * @param string   $char Separator
     * 
     * @return array
     * 
     * @throws Exception
     */
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