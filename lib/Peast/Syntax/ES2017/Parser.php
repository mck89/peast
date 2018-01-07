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
     * Array of keywords that depends on a context property
     * 
     * @var array 
     */
    protected $contextKeywords = array(
        "yield" => "allowYield",
        "await" => "allowAwait"
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
     * @param bool $checkFn If false it won't check if the async keyword is
     *                      followed by "function"
     * 
     * @return Token
     */
    protected function checkAsyncFunctionStart($checkFn = true)
    {
        return ($asyncToken = $this->scanner->getToken()) &&
               $asyncToken->getValue() === "async" &&
               (
                    !$checkFn ||
                    (($nextToken = $this->scanner->getNextToken()) &&
                    $nextToken->getValue() === "function")
               ) &&
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
            $id = $this->parseIdentifier(static::$bindingIdentifier);
            
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
            
            if ($generator) {
                $flags = array(null, "allowYield" => true);
            } elseif ($async) {
                $flags = array(null, "allowAwait" => true);
            } else {
                $flags = null;
            }
            
            $id = $this->isolateContext(
                $flags,
                "parseIdentifier",
                array(static::$bindingIdentifier)
            );
            
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
        } elseif ($token = $this->checkAsyncFunctionStart(false)) {
            $this->scanner->consumeToken();
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
                
                if ($generator) {
                    $flags = array(null, "allowYield" => true);
                } elseif ($async) {
                    $flags = array(null, "allowAwait" => true);
                } else {
                    $flags = null;
                }
                
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
                        $flags, "parseFormalParameterList"
                    );
                }

                if ($params !== null &&
                    $this->scanner->consume(")") &&
                    ($tokenBodyStart = $this->scanner->consume("{")) &&
                    (($body = $this->isolateContext(
                        $flags, "parseFunctionBody"
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
    
    /**
     * Parses the body of an arrow function. The returned value is an array
     * where the first element is the function body and the second element is
     * a boolean indicating if the body is wrapped in curly braces
     * 
     * @param bool  $async  Async body mode
     * 
     * @return array|null
     */
    protected function parseConciseBody($async = false)
    {
        if ($token = $this->scanner->consume("{")) {
            
            if (($body = $this->isolateContext(
                    $async ? array(null, "allowAwait" => true) : null,
                    "parseFunctionBody"
                )) &&
                $this->scanner->consume("}")
            ) {
                $body->setStartPosition($token->getLocation()->getStart());
                $body->setEndPosition($this->scanner->getPosition());
                return array($body, false);
            }
            
            return $this->error();
        } elseif (!$this->scanner->isBefore(array("{")) &&
            $body = $this->isolateContext(
                array("allowYield" => false, "allowAwait" => $async),
                "parseAssignmentExpression"
            )
        ) {
            return array($body, true);
        }
        return null;
    }
    
    /**
     * Parses an arrow function
     * 
     * @return Node\ArrowFunctionExpression|null
     */
    protected function parseArrowFunction()
    {
        $state = $this->scanner->getState();
        $async = false;
        if ($asyncToken = $this->checkAsyncFunctionStart(false)) {
            $this->scanner->consumeToken();
            $async = true;
        }
        if (($params = $this->parseArrowParameters()) !== null) {
            
            if ($this->scanner->noLineTerminators() &&
                $this->scanner->consume("=>")
            ) {
                
                if ($body = $this->parseConciseBody($async)) {
                    if (is_array($params)) {
                        $pos = $params[1];
                        $params = $params[0];
                    } else {
                        $pos = $params;
                        $params = array($params);
                    }
                    if ($async) {
                        $pos = $asyncToken;
                    }
                    $node = $this->createNode("ArrowFunctionExpression", $pos);
                    $node->setParams($params);
                    $node->setBody($body[0]);
                    $node->setExpression($body[1]);
                    $node->setAsync($async);
                    return $this->completeNode($node);
                }
            
                return $this->error();
            }
        }
        $this->scanner->setState($state);
        return null;
    }
    
    /**
     * Parses a for(var ...) statement
     * 
     * @param Token $forToken Token that corresponds to the "for" keyword
     * 
     * @return Node\Node|null
     */
    protected function parseForVarStatement($forToken)
    {
        if (!($varToken = $this->scanner->consume("var"))) {
            return null;
        }
            
        $state = $this->scanner->getState();
        
        if (($decl = $this->isolateContext(
                array("allowIn" => false), "parseVariableDeclarationList"
            )) &&
            ($varEndPosition = $this->scanner->getPosition()) &&
            $this->scanner->consume(";")
        ) {
                    
            $init = $this->createNode(
                "VariableDeclaration", $varToken
            );
            $init->setKind($init::KIND_VAR);
            $init->setDeclarations($decl);
            $init = $this->completeNode($init, $varEndPosition);
            
            $test = $this->isolateContext(
                array("allowIn" => true), "parseExpression"
            );
            
            if ($this->scanner->consume(";")) {
                
                $update = $this->isolateContext(
                    array("allowIn" => true), "parseExpression"
                );
                
                if ($this->scanner->consume(")") &&
                    $body = $this->parseStatement()
                ) {
                    
                    $node = $this->createNode("ForStatement", $forToken);
                    $node->setInit($init);
                    $node->setTest($test);
                    $node->setUpdate($update);
                    $node->setBody($body);
                    return $this->completeNode($node);
                }
            }
        } else {
            
            $this->scanner->setState($state);
            
            if ($decl = $this->parseForBinding()) {
                
                $init = $decl->getId()->getType() === "Identifier" ?
                        $this->parseInitializer() :
                        null;
                if ($init) {
                    $decl->setInit($init);
                    $decl->setEndPosition($init->getLocation()->getEnd());
                }
                
                $left = $this->createNode("VariableDeclaration", $varToken);
                $left->setKind($left::KIND_VAR);
                $left->setDeclarations(array($decl));
                $left = $this->completeNode($left);
                
                if ($this->scanner->consume("in")) {
                    
                    if ($init && $this->scanner->getStrictMode()) {
                        return $this->error(
                            "For-in variable initializer not allowed in " .
                            "strict mode"
                        );
                    }
                    
                    if (($right = $this->isolateContext(
                            array("allowIn" => true), "parseExpression"
                        )) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement()
                    ) {
                        
                        $node = $this->createNode(
                            "ForInStatement", $forToken
                        );
                        $node->setLeft($left);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                    }
                } elseif (!$init && $this->scanner->consume("of")) {
                    
                    if (($right = $this->isolateContext(
                            array("allowIn" => true), "parseAssignmentExpression"
                        )) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement()
                    ) {
                        
                        $node = $this->createNode(
                            "ForOfStatement", $forToken
                        );
                        $node->setLeft($left);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                    }
                }
            }
        }
        
        return $this->error();
    }
}