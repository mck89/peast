<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2018;

use \Peast\Syntax\Node;

/**
 * ES2018 parser class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Parser extends \Peast\Syntax\ES2017\Parser
{
    /**
     * Checks if the given string or number contains invalid esape sequences
     * 
     * @param string  $val                      Value to check
     * @param bool    $number                   True if the value is a number
     * @param bool    $forceLegacyOctalCheck    True to force legacy octal
     *                                          form check
     * @param bool    $taggedTemplate           True if the value is a tagged
     *                                          template
     * 
     * @return void
     */
    protected function checkInvalidEscapeSequences(
        $val, $number = false, $forceLegacyOctalCheck = false,
        $taggedTemplate = false
    ) {
        if (!$taggedTemplate) {
            parent::checkInvalidEscapeSequences(
                $val, $number, $forceLegacyOctalCheck, $taggedTemplate
            );
        }
    }
    
    /**
     * Parses an object binding pattern
     * 
     * @return Node\ObjectPattern|null
     */
    protected function parseObjectBindingPattern()
    {
        $state = $this->scanner->getState();
        if ($token = $this->scanner->consume("{")) {
            
            $properties = array();
            while ($prop = $this->parseBindingProperty()) {
                $properties[] = $prop;
                if (!$this->scanner->consume(",")) {
                    break;
                }
            }
            
            if ($rest = $this->parseRestProperty()) {
                $properties[] = $rest;
            }
            
            if ($this->scanner->consume("}")) {
                $node = $this->createNode("ObjectPattern", $token);
                if ($properties) {
                    $node->setProperties($properties);
                }
                return $this->completeNode($node);
            }
            
            $this->scanner->setState($state);
        }
        return null;
    }
    
    /**
     * Parses a rest property
     * 
     * @return Node\RestElement|null
     */
    protected function parseRestProperty()
    {
        if ($token = $this->scanner->consume("...")) {
            
            if ($argument = $this->parseIdentifier(static::$bindingIdentifier)) {
                $node = $this->createNode("RestElement", $token);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    /**
     * Parses a property in an object literal
     * 
     * @return Node\Property|Node\SpreadElement|null
     */
    protected function parsePropertyDefinition()
    {
        if ($prop = $this->parseSpreadElement()) {
            return $prop;
        }
        return parent::parsePropertyDefinition();
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
            $async = true;
        }
        if ($token = $this->scanner->consume("function")) {
            
            $generator = $allowGenerator && $this->scanner->consume("*");
            $id = $this->parseIdentifier(static::$bindingIdentifier);
            
            if ($generator || $async) {
                $flags = array(null);
                if ($generator) {
                    $flags["allowYield"] = true;
                }
                if ($async) {
                    $flags["allowAwait"] = true;
                }
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
        $async = false;
        if ($asyncToken = $this->checkAsyncFunctionStart()) {
            $this->scanner->consumeToken();
            $async = true;
        }
        if ($token = $this->scanner->consume("function")) {
            
            $generator = $this->scanner->consume("*");
            
            if ($generator || $async) {
                $flags = array(null);
                if ($generator) {
                    $flags["allowYield"] = true;
                }
                if ($async) {
                    $flags["allowAwait"] = true;
                }
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
            if ($token = $this->scanner->consume("*")) {
                $generator = true;
            }
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
                
                if ($generator || $async) {
                    $flags = array(null);
                    if ($generator) {
                        $flags["allowYield"] = true;
                    }
                    if ($async) {
                        $flags["allowAwait"] = true;
                    }
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
     * Parses do-while, while, for, for-in and for-of statements
     * 
     * @return Node\Node|null
     */
    protected function parseIterationStatement()
    {
        if ($node = $this->parseWhileStatement()) {
            return $node;
        } elseif ($node = $this->parseDoWhileStatement()) {
            return $node;
        } elseif ($startForToken = $this->scanner->consume("for")) {
                
            $forAwait = false;
            if ($this->context->allowAwait &&
                $this->scanner->consume("await")
            ) {
                $forAwait = true;
            }
            
            if ($this->scanner->consume("(") && (
                ($node = $this->parseForVarStatement($startForToken)) ||
                ($node = $this->parseForLetConstStatement($startForToken)) ||
                ($node = $this->parseForNotVarLetConstStatement($startForToken)))
            ) {
                if ($forAwait) {
                    if (!$node instanceof Node\ForOfStatement) {
                        $this->error(
                            "Async iteration is allowed only with for-of statements",
                            $startForToken->getLocation()->getStart()
                        );
                    }
                    $node->setAwait(true);
                }
                return $node;
            }
            
            return $this->error();
        }
        
        return null;
    }
}