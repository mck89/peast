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
use \Peast\Syntax\Token;

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
        "export" => array(
            "tokens"=> array("function", "class", array("async", true)),
            "next"=> true
        ),
        "expression" => array(
            "tokens"=> array(
                "{", "function", "class", array("async", true), array("let", "[")
            ),
            "next"=> true
        )
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
     * Parses an identifier
     * 
     * @param int   $mode       Parsing mode, one of the id parsing mode
     *                          constants
     * @param string $after     If a string is passed in this parameter, the
     *                          identifier is parsed only if preceeds this string
     * 
     * @return Node\Identifier|null
     */
    protected function parseIdentifier($mode, $after = null)
    {
        $token = $this->scanner->getToken();
        if (!$token) {
            return null;
        }
        if ($after !== null) {
            $next = $this->scanner->getNextToken();
            if (!$next || $next->getValue() !== $after) {
                return null;
            }
        }
        $type = $token->getType();
        switch ($type) {
            case Token::TYPE_BOOLEAN_LITERAL:
            case Token::TYPE_NULL_LITERAL:
                if ($mode !== self::ID_ALLOW_ALL) {
                    return null;
                }
            break;
            case Token::TYPE_KEYWORD:
                if ($mode === self::ID_ALLOW_NOTHING) {
                    return null;
                } elseif ($mode === self::ID_MIXED &&
                    $this->scanner->isStrictModeKeyword($token)
                ) {
                    return null;
                }
            break;
            default:
                if ($type !== Token::TYPE_IDENTIFIER) {
                    return null;
                } elseif ($mode !== self::ID_ALLOW_ALL &&
                    $this->context->allowAwait &&
                    $token->getValue() === "await"
                ) {
                    return null;
                }
            break;
        }
        $this->scanner->consumeToken();
        $node = $this->createNode("Identifier", $token);
        $node->setName($token->getValue());
        return $this->completeNode($node);
    }
    
    /**
     * Parses a unary expression
     * 
     * @return Node\Node|null
     */
    protected function parseUnaryExpression()
    {
        $operators = $this->unaryOperators;
        if ($this->context->allowAwait) {
            $operators[] = "await";
        }
        if ($expr = $this->parsePostfixExpression()) {
            return $expr;
        } elseif ($token = $this->scanner->consumeOneOf($operators)) {
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
     * Checks if an async function can start from the current position. Returns
     * the async token or null if not found
     * 
     * @return Token
     */
    protected function checkAsyncFunctionStart()
    {
        return ($asyncToken = $this->scanner->getToken()) &&
               $asyncToken->getValue() === "async" &&
               ($nextToken = $this->scanner->getNextToken()) &&
               $nextToken->getValue() === "function" &&
               $this->scanner->noLineTerminators(true) ?
               $asyncToken :
               null;
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
        if ($asyncToken = $this->checkAsyncFunctionStart()) {
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
                $node = $this->createNode(
                    "FunctionDeclaration",
                    $async ? $asyncToken : $token
                );
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
        if ($asyncToken = $this->checkAsyncFunctionStart()) {
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
                $node = $this->createNode(
                    "FunctionExpression",
                    $async ? $asyncToken : $token
                );
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
    
    /**
     * Parses a method definition
     * 
     * @return Node\MethodDefinition|null
     */
    protected function parseMethodDefinition()
    {
        $state = $this->scanner->getState();
        $generator = $error = $async = false;
        $position = null;
        $kind = Node\MethodDefinition::KIND_METHOD;
        if ($token = $this->scanner->consume("get")) {
            $position = $token;
            $kind = Node\MethodDefinition::KIND_GET;
            $error = true;
        } elseif ($token = $this->scanner->consume("set")) {
            $position = $token;
            $kind = Node\MethodDefinition::KIND_SET;
            $error = true;
        } elseif ($token = $this->scanner->consume("*")) {
            $position = $token;
            $error = true;
            $generator = true;
        } elseif ($token = $this->scanner->consume("async")) {
            if (!$this->scanner->noLineTerminators()) {
                return $this->error();
            }
            $position = $token;
            $error = true;
            $async = true;
        }
        
        //Handle the case where get and set are methods name and not the
        //definition of a getter/setter
        if ($kind !== Node\MethodDefinition::KIND_METHOD &&
            $this->scanner->consume("(")
        ) {
            $this->scanner->setState($state);
            $kind = Node\MethodDefinition::KIND_METHOD;
            $error = false;
        }
        
        if ($prop = $this->parsePropertyName()) {
            
            if (!$position) {
                $position = isset($prop[2]) ? $prop[2] : $prop[0];
            }
            if ($tokenFn = $this->scanner->consume("(")) {
                
                $error = true;
                $params = array();
                if ($kind === Node\MethodDefinition::KIND_SET) {
                    $params = $this->isolateContext(
                        null, "parseBindingElement"
                    );
                    if ($params) {
                        $params = array($params);
                    }
                } elseif ($kind === Node\MethodDefinition::KIND_METHOD) {
                    $params = $this->isolateContext(
                        null, "parseFormalParameterList"
                    );
                }
                
                if ($generator) {
                    $flags = array(null, "allowYield" => true);
                } elseif ($async) {
                    $flags = array(null, "allowAwait" => true);
                } else {
                    $flags = null;
                }

                if ($params !== null &&
                    $this->scanner->consume(")") &&
                    ($tokenBodyStart = $this->scanner->consume("{")) &&
                    (($body = $this->isolateContext(
                        $flags,
                        "parseFunctionBody"
                    )) || true) &&
                    $this->scanner->consume("}")
                ) {

                    if ($prop[0] instanceof Node\Identifier &&
                        $prop[0]->getName() === "constructor"
                    ) {
                        $kind = Node\MethodDefinition::KIND_CONSTRUCTOR;
                    }

                    $body->setStartPosition(
                        $tokenBodyStart->getLocation()->getStart()
                    );
                    $body->setEndPosition($this->scanner->getPosition());
                    
                    $nodeFn = $this->createNode("FunctionExpression", $tokenFn);
                    $nodeFn->setParams($params);
                    $nodeFn->setBody($body);
                    $nodeFn->setGenerator($generator);
                    $nodeFn->setAsync($async);

                    $node = $this->createNode("MethodDefinition", $position);
                    $node->setKey($prop[0]);
                    $node->setValue($this->completeNode($nodeFn));
                    $node->setKind($kind);
                    $node->setComputed($prop[1]);
                    return $this->completeNode($node);
                }
            }
        }
        
        if ($error) {
            return $this->error();
        } else {
            $this->scanner->setState($state);
        }
        return null;
    }
}