<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2017;

use \Peast\Syntax\Node;

/**
 * ES2017 parser class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Parser extends \Peast\Syntax\ES2016\Parser
{
    /**
     * Configurable lookaheads
     * 
     * @var array 
     */
    protected $lookahead = array(
        "export" => array("function", "class", "async"),
        "expression" => array("{", "function", "class", "async", array("let", "["))
    );
    
    /**
     * Unary operators
     * 
     * @var array 
     */
    protected $unaryOperators = array(
        "await", "delete", "void", "typeof", "++", "--", "+", "-", "~", "!"
    );
    
    /**
     * Initializes parser context
     * 
     * @return void
     */
    protected function initContext()
    {
        parent::initContext();
        $this->context->allowAwait = false;
    }
    
    /**
     * Parses an arguments list
     * 
     * @return array|null
     */
    protected function parseArgumentList()
    {
        $list = array();
        while (true) {
            $spread = $this->scanner->consume("...");
            $exp = $this->isolateContext(
                array("allowIn" => true), "parseAssignmentExpression"
            );
            if (!$exp) {
                if ($spread) {
                    return $this->error();
                }
                break;
            }
            if ($spread) {
                $node = $this->createNode("SpreadElement", $spread);
                $node->setArgument($exp);
                $list[] = $this->completeNode($node);
            } else {
                $list[] = $exp;
            }
            if (!$this->scanner->consume(",")) {
                break;
            }
        }
        return $list;
    }
    
    /**
     * Parses a parameter list
     * 
     * @return Node\Node[]|null
     */
    protected function parseFormalParameterList()
    {
        $list = array();
        while (
            ($param = $this->parseBindingRestElement()) ||
            $param = $this->parseBindingElement()
        ) {
            $list[] = $param;
            if ($param->getType() === "RestElement" ||
                !$this->scanner->consume(",")) {
                break;
            }
        }
        return $list;
    }
    
    /**
     * Parses a unary expression
     * 
     * @return Node\Node|null
     */
    protected function parseUnaryExpression()
    {
        if ($expr = $this->parsePostfixExpression()) {
            return $expr;
        } elseif ($token = $this->scanner->consumeOneOf($this->unaryOperators)) {
            if ($argument = $this->parseUnaryExpression()) {
                
                $op = $token->getValue();
                
                //Deleting a variable without accessing its properties is a
                //syntax error in strict mode
                if ($op === "delete" &&
                    $this->scanner->getStrictMode() &&
                    $argument instanceof Node\Identifier) {
                    return $this->error(
                        "Deleting an unqualified identifier is not allowed in strict mode"
                    );
                }
                
                if ($op === "await") {
                    $node = $this->createNode("AwaitExpression", $token);
                } else {
                    if ($op === "++" || $op === "--") {
                        $node = $this->createNode("UpdateExpression", $token);
                        $node->setPrefix(true);
                    } else {
                        $node = $this->createNode("UnaryExpression", $token);
                    }
                    $node->setOperator($op);
                }
                $node->setArgument($argument);
                return $this->completeNode($node);
            }

            return $this->error();
        }
        return null;
    }
    
    /**
     * Checks if an async function can start from the current position
     * 
     * @return bool
     */
    protected function checkAsyncFunctionStart()
    {
        return ($asyncToken = $this->scanner->getToken()) &&
               $asyncToken->getValue() === "async" &&
               ($nextToken = $this->scanner->getNextToken()) &&
               $nextToken->getValue() === "function" &&
               $this->scanner->noLineTerminators(true);
    }
    
    /**
     * Parses function or generator declaration
     * 
     * @param bool $default        Default mode
     * @param bool $allowGenerator True to allow parsing of generators
     * 
     * @return Node\FunctionDeclaration|null
     */
    protected function parseFunctionOrGeneratorDeclaration(
        $default = false, $allowGenerator = true
    ) {
        $async = false;
        if ($this->checkAsyncFunctionStart()) {
            $this->scanner->consumeToken();
            $allowGenerator = false;
            $async = true;
        }
        if ($token = $this->scanner->consume("function")) {
            
            $generator = $allowGenerator && $this->scanner->consume("*");
            $id = $this->parseIdentifier(self::ID_MIXED);
            
            if ($generator) {
                $flags = array(null, "allowYield" => true);
            } elseif ($async) {
                $flags = array(null, "allowAwait" => true);
            } else {
                $flags = null;
            }
            
            if (($default || $id) &&
                $this->scanner->consume("(") &&
                ($params = $this->isolateContext(
                    $flags,
                    "parseFormalParameterList"
                )) !== null &&
                $this->scanner->consume(")") &&
                ($tokenBodyStart = $this->scanner->consume("{")) &&
                (($body = $this->isolateContext(
                    $flags,
                    "parseFunctionBody"
                )) || true) &&
                $this->scanner->consume("}")
            ) {
                
                $body->setStartPosition(
                    $tokenBodyStart->getLocation()->getStart()
                );
                $body->setEndPosition($this->scanner->getPosition());
                $node = $this->createNode("FunctionDeclaration", $token);
                if ($id) {
                    $node->setId($id);
                }
                $node->setParams($params);
                $node->setBody($body);
                $node->setGenerator($generator);
                $node->setAsync($async);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    /**
     * Parses function or generator expression
     * 
     * @return Node\FunctionExpression|null
     */
    protected function parseFunctionOrGeneratorExpression()
    {
        $allowGenerator = true;
        $async = false;
        if ($this->checkAsyncFunctionStart()) {
            $this->scanner->consumeToken();
            $allowGenerator = false;
            $async = true;
        }
        if ($token = $this->scanner->consume("function")) {
            
            $generator = $allowGenerator && $this->scanner->consume("*");
            $id = $this->parseIdentifier(self::ID_MIXED);
            
            if ($generator) {
                $flags = array(null, "allowYield" => true);
            } elseif ($async) {
                $flags = array(null, "allowAwait" => true);
            } else {
                $flags = null;
            }
            
            if ($this->scanner->consume("(") &&
                ($params = $this->isolateContext(
                    $flags,
                    "parseFormalParameterList"
                )) !== null &&
                $this->scanner->consume(")") &&
                ($tokenBodyStart = $this->scanner->consume("{")) &&
                (($body = $this->isolateContext(
                    $flags,
                    "parseFunctionBody"
                )) || true) &&
                $this->scanner->consume("}")
            ) {
                
                $body->setStartPosition(
                    $tokenBodyStart->getLocation()->getStart()
                );
                $body->setEndPosition($this->scanner->getPosition());
                $node = $this->createNode("FunctionExpression", $token);
                $node->setId($id);
                $node->setParams($params);
                $node->setBody($body);
                $node->setGenerator($generator);
                $node->setAsync($async);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
}