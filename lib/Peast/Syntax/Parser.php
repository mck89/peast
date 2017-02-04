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
 * 
 * @abstract
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
     * Parser context
     * 
     * @var stdClass 
     */
    protected $context;
    
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
        $this->initContext();
    }
    
    /**
     * Initializes parser context
     * 
     * @return stdClass
     */
    abstract protected function initContext();
    
    /**
     * Parses the source
     * 
     * @return Node\Node
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
     * Calls a method with an isolated parser context, applyng the given flags,
     * but restoring their values after the execution.
     * 
     * @param array|null  $flags  Key/value array of changes to apply to the
     *                            context flags. If it's null or the first
     *                            element of the array is null the context will
*                                 be reset before applying new values.
     * @param string      $fn     Method to call
     * @param array       $args   Method arguments
     * 
     * @return mixed
     */
    protected function isolateContext($flags, $fn, $args = array())
    {
        //Store the current context
        $oldContext = clone $this->context;
        
        //If flag argument is null reset the context
        if ($flags === null) {
            $this->initContext();
        } else {
            //If flag argument is an array and the first element is null reset
            //the context
            if (isset($flags[0]) && $flags[0] === null) {
                //$this->initContext();
                array_shift($flags);
            }
            //Apply new values to the flags
            foreach ($flags as $k => $v) {
                $this->context->$k = $v;
            }
        }
        
        //Call the method with the given arguments
        $ret = call_user_func_array(array($this, $fn), $args);
        
        //Restore previous context
        $this->context = $oldContext;
        
        return $ret;
    }
    
    /**
     * Creates a node
     * 
     * @param string $nodeType Node's type
     * @param mixed  $position Node's start position
     * 
     * @return Node\Node
     * 
     * @codeCoverageIgnore
     */
    protected function createNode($nodeType, $position)
    {
        $nodeClass = "\\Peast\\Syntax\\Node\\" . $nodeType;
        $node = new $nodeClass;
        if ($position instanceof Node\Node || $position instanceof Token) {
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
     * @param Node\Node   $node     Node to complete
     * @param Position    $position Node's end position
     * 
     * @return Node\Node
     * 
     * @codeCoverageIgnore
     */
    protected function completeNode(Node\Node $node, $position = null)
    {
        return $node->setEndPosition(
            $position ? $position : $this->scanner->getPosition()
        );
    }
    
    /**
     * Throws a syntax error
     * 
     * @param string   $message  Error message
     * @param Position $position Error position
     * 
     * @return void
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
            return $this->error();
        }
        return $list;
    }
}