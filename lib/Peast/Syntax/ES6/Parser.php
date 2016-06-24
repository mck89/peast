<?php
namespace Peast\Syntax\ES6;

use Peast\Syntax\Token;

class Parser extends \Peast\Syntax\Parser
{
    public function parse()
    {
        $type = isset($this->options["sourceType"]) ?
                $this->options["sourceType"] :
                \Peast\Peast::SOURCE_TYPE_SCRIPT;
        
        $body = $type === \Peast\Peast::SOURCE_TYPE_MODULE ?
                $this->parseModuleItemList() :
                $this->parseStatementList();
        
        $node = $this->createNode(
            "Program", $body ? $body : $this->scanner->getPosition()
        );
        $node->setSourceType($type);
        if ($body) {
            $node->setBody($body);
        }
        $program = $this->completeNode($node);
        if ($this->scanner->getToken()) {
            return $this->error();
        }
        return $program;
    }
    
    protected function expressionToPattern($node)
    {
        $retNode = $node;
        if ($node instanceof Node\ArrayExpression) {
            
            $loc = $node->getLocation();
            $elems = array();
            foreach ($node->getElements() as $elem) {
                $elems[] = $this->expressionToPattern($elem);
            }
                
            $retNode = $this->createNode("ArrayPattern", $loc->getStart());
            $retNode->setElements($elems);
            $this->completeNode($retNode, $loc->getEnd());
            
        } elseif ($node instanceof Node\ObjectExpression) {
            
            $loc = $node->getLocation();
            $props = array();
            foreach ($node->getProperties() as $prop) {
                $props[] = $this->expressionToPattern($prop);
            }
                
            $retNode = $this->createNode("ObjectPattern", $loc->getStart());
            $retNode->setProperties($props);
            $this->completeNode($retNode, $loc->getEnd());
            
        } elseif ($node instanceof Node\Property) {
            
            $loc = $node->getLocation();
            $retNode = $this->createNode("AssignmentProperty", $loc->getStart());
            $retNode->setValue($node->getValue());
            $retNode->setKey($node->getKey());
            $retNode->setMethod($node->getMethod());
            $retNode->setShorthand($node->getShorthand());
            $retNode->setComputed($node->getComputed());
            $this->completeNode($retNode, $loc->getEnd());
            
        } elseif ($node instanceof Node\SpreadElement) {
            
            $loc = $node->getLocation();
            $retNode = $this->createNode("RestElement", $loc->getStart());
            $retNode->setArgument($this->expressionToPattern($node->getArgument()));
            $this->completeNode($retNode, $loc->getEnd());
            
        } elseif ($node instanceof Node\AssignmentExpression) {
            
            $loc = $node->getLocation();
            $retNode = $this->createNode("AssignmentPattern", $loc->getStart());
            $retNode->setLeft($this->expressionToPattern($node->getLeft()));
            $retNode->setRight($node->getRight());
            $this->completeNode($retNode, $loc->getEnd());
            
        }
        return $retNode;
    }
    
    protected function parseStatementList($yield = false, $return = false)
    {
        $items = array();
        while ($item = $this->parseStatementListItem($yield, $return)) {
            $items[] = $item;
        }
        return count($items) ? $items : null;
    }
    
    protected function parseStatementListItem($yield = false, $return = false)
    {
        if ($declaration = $this->parseDeclaration($yield)) {
            return $declaration;
        } elseif ($statement = $this->parseStatement($yield, $return)) {
            return $statement;
        }
        return null;
    }
    
    protected function parseStatement($yield = false, $return = false)
    {
        if ($statement = $this->parseBlock($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseVariableStatement($yield)) {
            return $statement;
        } elseif ($statement = $this->parseEmptyStatement()) {
            return $statement;
        } elseif ($statement = $this->parseIfStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseBreakableStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseContinueStatement($yield)) {
            return $statement;
        } elseif ($statement = $this->parseBreakStatement($yield)) {
            return $statement;
        } elseif ($return && $statement = $this->parseReturnStatement($yield)) {
            return $statement;
        } elseif ($statement = $this->parseWithStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseThrowStatement($yield)) {
            return $statement;
        } elseif ($statement = $this->parseTryStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseDebuggerStatement()) {
            return $statement;
        } elseif ($statement = $this->parseLabelledStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseExpressionStatement($yield)) {
            return $statement;
        }
        return null;
    }
    
    protected function parseDeclaration($yield = false)
    {
        if ($declaration = $this->parseFunctionOrGeneratorDeclaration($yield)) {
            return $declaration;
        } elseif ($declaration = $this->parseClassDeclaration($yield)) {
            return $declaration;
        } elseif ($declaration = $this->parseLexicalDeclaration(true, $yield)) {
            return $declaration;
        }
        return null;
    }
    
    protected function parseBreakableStatement($yield = false, $return = false)
    {
        if ($statement = $this->parseIterationStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseSwitchStatement($yield, $return)) {
            return $statement;
        }
        return null;
    }
    
    protected function parseBlock($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("{")) {
            
            $statements = $this->parseStatementList($yield, $return);
            if ($this->scanner->consume("}")) {
                $node = $this->createNode("BlockStatement", $token);
                if ($statements) {
                    $node->setBody($statements);
                }
                return $this->completeNode($node);
            }
            return $this->error();
        }
        return null;
    }
    
    protected function parseModuleItemList()
    {
        $items = array();
        while ($item = $this->parseModuleItem()) {
            $items[] = $item;
        }
        return count($items) ? $items : null;
    }
    
    protected function parseEmptyStatement()
    {
        if ($token = $this->scanner->consume(";")) {
            $node = $this->createNode("EmptyStatement", $token);
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseDebuggerStatement()
    {
        if ($token = $this->scanner->consume("debugger")) {
            $node = $this->createNode("DebuggerStatement", $token);
            $this->assertEndOfStatement();
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseIfStatement($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("if")) {
            
            if ($this->scanner->consume("(") &&
                ($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                $consequent = $this->parseStatement($yield, $return)) {
                
                $node = $this->createNode("IfStatement", $token);
                $node->setTest($test);
                $node->setConsequent($consequent);
                
                if ($this->scanner->consume("else")) {
                    if ($alternate = $this->parseStatement($yield, $return)) {
                        $node->setAlternate($alternate);
                        return $this->completeNode($node);
                    }
                } else {
                    return $this->completeNode($node);
                }
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseTryStatement($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("try")) {
            
            if ($block = $this->parseBlock($yield, $return)) {
                
                $node = $this->createNode("TryStatement", $token);
                $node->setBlock($block);

                if ($handler = $this->parseCatch($yield, $return)) {
                    $node->setHandler($handler);
                }

                if ($finalizer = $this->parseFinally($yield, $return)) {
                    $node->setFinalizer($finalizer);
                }

                if ($handler || $finalizer) {
                    return $this->completeNode($node);
                }
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseCatch($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("catch")) {
            
            if ($this->scanner->consume("(") &&
                ($param = $this->parseCatchParameter($yield)) &&
                $this->scanner->consume(")") &&
                $body = $this->parseBlock($yield, $return)) {

                $node = $this->createNode("CatchClause", $token);
                $node->setParam($param);
                $node->setBody($body);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseCatchParameter($yield = false)
    {
        if ($param = $this->parseIdentifier($yield)) {
            return $param;
        } elseif ($param = $this->parseBindingPattern($yield)) {
            return $param;
        }
        return null;
    }
    
    protected function parseFinally($yield = false, $return = false)
    {
        if ($this->scanner->consume("finally")) {
            
            if ($block = $this->parseBlock($yield, $return)) {
                return $block;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseContinueStatement($yield = false)
    {
        if ($token = $this->scanner->consume("continue")) {
            
            $node = $this->createNode("ContinueStatement", $token);
            
            if ($this->scanner->noLineTerminators()) {
                if ($label = $this->parseIdentifier($yield)) {
                    $node->setLabel($label);
                }
            }
            
            $this->assertEndOfStatement();
            
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseBreakStatement($yield = false)
    {
        if ($token = $this->scanner->consume("break")) {
            
            $node = $this->createNode("BreakStatement", $token);
            
            if ($this->scanner->noLineTerminators()) {
                if ($label = $this->parseIdentifier($yield)) {
                    $node->setLabel($label);
                }
            }
            
            $this->assertEndOfStatement();
            
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseReturnStatement($yield = false)
    {
        if ($token = $this->scanner->consume("return")) {
            
            $node = $this->createNode("ReturnStatement", $token);
            
            if ($this->scanner->noLineTerminators()) {
                if ($argument = $this->parseExpression(true, $yield)) {
                    $node->setArgument($argument);
                }
            }
            
            $this->assertEndOfStatement();
            
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseLabelledStatement($yield = false, $return = false)
    {
        $state = $this->scanner->getState();
        if ($label = $this->parseIdentifier($yield)) {
            
            if ($this->scanner->consume(":")) {
                
                if (($body = $this->parseStatement($yield, $return)) ||
                    ($body = $this->parseFunctionOrGeneratorDeclaration(
                        $yield, false, false
                    ))) {
                    
                    $node = $this->createNode("LabeledStatement", $label);
                    $node->setLabel($label);
                    $node->setBody($body);
                    return $this->completeNode($node);
                    
                }
                
                return $this->error();
            }
            
            $this->scanner->setState($state);
        }
        return null;
    }
    
    protected function parseThrowStatement($yield = false)
    {
        if ($token = $this->scanner->consume("throw")) {
            
            if ($this->scanner->noLineTerminators() &&
                ($argument = $this->parseExpression(true, $yield))) {
                
                $this->assertEndOfStatement();
                $node = $this->createNode("ThrowStatement", $token);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseWithStatement($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("with")) {
            
            if ($this->scanner->consume("(") &&
                ($object = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                $body = $this->parseStatement($yield, $return)) {
            
                $node = $this->createNode("WithStatement", $token);
                $node->setObject($object);
                $node->setBody($body);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseSwitchStatement($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("switch")) {
            
            if ($this->scanner->consume("(") &&
                ($discriminant = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                ($cases = $this->parseCaseBlock($yield, $return)) !== null) {
            
                $node = $this->createNode("SwitchStatement", $token);
                $node->setDiscriminant($discriminant);
                $node->setCases($cases);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseCaseBlock($yield = false, $return = false)
    {
        if ($this->scanner->consume("{")) {
            
            $parsedCasesAll = array(
                $this->parseCaseClauses($yield, $return),
                $this->parseDefaultClause($yield, $return),
                $this->parseCaseClauses($yield, $return)
            );
            
            if ($this->scanner->consume("}")) {
                $cases = array();
                foreach ($parsedCasesAll as $parsedCases) {
                    if ($parsedCases) {
                        if (is_array($parsedCases)) {
                            $cases = array_merge($cases, $parsedCases);
                        } else {
                            $cases[] = $parsedCases;
                        }
                    }
                }
                return $cases;
            } elseif ($this->parseDefaultClause($yield, $return)) {
                return $this->error("Multiple default clause in switch statement");
            } else {
                return $this->error();
            }
        }
        return null;
    }
    
    protected function parseCaseClauses($yield = false, $return = false)
    {
        $cases = array();
        while ($case = $this->parseCaseClause($yield, $return)) {
            $cases[] = $case;
        }
        return count($cases) ? $cases : null;
    }
    
    protected function parseCaseClause($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("case")) {
            
            if (($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(":")) {

                $node = $this->createNode("SwitchCase", $token);
                $node->setTest($test);

                if ($consequent = $this->parseStatementList($yield, $return)) {
                    $node->setConsequent($consequent);
                }

                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseDefaultClause($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("default")) {
            
            if ($this->scanner->consume(":")) {

                $node = $this->createNode("SwitchCase", $token);
            
                if ($consequent = $this->parseStatementList($yield, $return)) {
                    $node->setConsequent($consequent);
                }

                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseExpressionStatement($yield = false)
    {
        $lookahead = array("{", "function", "class", array("let", "["));
        if (!$this->scanner->isBefore($lookahead, true) &&
            $expression = $this->parseExpression(true, $yield)) {
            
            $this->assertEndOfStatement();
            $node = $this->createNode("ExpressionStatement", $expression);
            $node->setExpression($expression);
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseIterationStatement($yield = false, $return = false)
    {
        if ($token = $this->scanner->consume("do")) {
            
            if (($body = $this->parseStatement($yield, $return)) &&
                $this->scanner->consume("while") &&
                $this->scanner->consume("(") &&
                ($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")")) {
                    
                $node = $this->createNode("DoWhileStatement", $token);
                $node->setBody($body);
                $node->setTest($test);
                return $this->completeNode($node);
            }
            return $this->error();
            
        } elseif ($token = $this->scanner->consume("while")) {
            
            if ($this->scanner->consume("(") &&
                ($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                $body = $this->parseStatement($yield, $return)) {
                    
                $node = $this->createNode("WhileStatement", $token);
                $node->setTest($test);
                $node->setBody($body);
                return $this->completeNode($node);
            }
            return $this->error();
            
        } elseif ($token = $this->scanner->consume("for")) {
            
            $hasBracket = $this->scanner->consume("(");
            $afterBracketState = $this->scanner->getState();
            
            if (!$hasBracket) {
                return $this->error();
            } elseif ($varToken = $this->scanner->consume("var")) {
                
                $state = $this->scanner->getState();
                
                if (($decl = $this->parseVariableDeclarationList(false, $yield)) &&
                    ($varEndPosition = $this->scanner->getPosition()) &&
                    $this->scanner->consume(";")) {
                            
                    $init = $this->createNode(
                        "VariableDeclaration", $varToken
                    );
                    $init->setKind($init::KIND_VAR);
                    $init->setDeclarations($decl);
                    $init = $this->completeNode($init, $varEndPosition);
                    
                    $test = $this->parseExpression(true, $yield);
                    
                    if ($this->scanner->consume(";")) {
                        
                        $update = $this->parseExpression(true, $yield);
                        
                        if ($this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $node = $this->createNode("ForStatement", $token);
                            $node->setInit($init);
                            $node->setTest($test);
                            $node->setUpdate($update);
                            $node->setBody($body);
                            return $this->completeNode($node);
                        }
                    }
                } else {
                    
                    $this->scanner->setState($state);
                    
                    if ($decl = $this->parseForBinding($yield)) {
                        
                        $left = $this->createNode(
                            "VariableDeclaration", $varToken
                        );
                        $left->setKind($left::KIND_VAR);
                        $left->setDeclarations(array($decl));
                        $left = $this->completeNode($left);
                        
                        if ($this->scanner->consume("in")) {
                            
                            if (($right = $this->parseExpression(true, $yield)) &&
                                $this->scanner->consume(")") &&
                                $body = $this->parseStatement($yield, $return)) {
                                
                                $node = $this->createNode(
                                    "ForInStatement", $token
                                );
                                $node->setLeft($left);
                                $node->setRight($right);
                                $node->setBody($body);
                                return $this->completeNode($node);
                            }
                        } elseif ($this->scanner->consume("of")) {
                            
                            if (($right = $this->parseAssignmentExpression(true, $yield)) &&
                                $this->scanner->consume(")") &&
                                $body = $this->parseStatement($yield, $return)) {
                                
                                $node = $this->createNode(
                                    "ForOfStatement", $token
                                );
                                $node->setLeft($left);
                                $node->setRight($right);
                                $node->setBody($body);
                                return $this->completeNode($node);
                            }
                        }
                    }
                }
            } elseif ($init = $this->parseForDeclaration($yield)) {
                
                if ($init && $this->scanner->consume("in")) {
                    if (($right = $this->parseExpression(true, $yield)) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement($yield, $return)) {
                        
                        $node = $this->createNode("ForInStatement", $token);
                        $node->setLeft($init);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                    }
                } elseif ($init && $this->scanner->consume("of")) {
                    if (($right = $this->parseAssignmentExpression(true, $yield)) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement($yield, $return)) {
                        
                        $node = $this->createNode("ForOfStatement", $token);
                        $node->setLeft($init);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                    }
                } else {
                    
                    $this->scanner->setState($afterBracketState);
                    if ($init = $this->parseLexicalDeclaration($yield)) {
                        
                        $test = $this->parseExpression(true, $yield);
                        if ($this->scanner->consume(";")) {
                                
                            $update = $this->parseExpression(true, $yield);
                            
                            if ($this->scanner->consume(")") &&
                                $body = $this->parseStatement($yield, $return)) {
                                
                                $node = $this->createNode(
                                    "ForStatement", $token
                                );
                                $node->setInit($init);
                                $node->setTest($test);
                                $node->setUpdate($update);
                                $node->setBody($body);
                                return $this->completeNode($node);
                            }
                        }
                    }
                }
                
            } elseif (!$this->scanner->isBefore(array("let"))) {
                
                $state = $this->scanner->getState();
                $notBeforeSB = !$this->scanner->isBefore(array(array("let", "[")), true);
                
                if ($notBeforeSB &&
                    (($init = $this->parseExpression(false, $yield)) || true) &&
                    $this->scanner->consume(";")) {
                
                    $test = $this->parseExpression(true, $yield);
                    
                    if ($this->scanner->consume(";")) {
                            
                        $update = $this->parseExpression(true, $yield);
                        
                        if ($this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $node = $this->createNode(
                                "ForStatement", $token
                            );
                            $node->setInit($init);
                            $node->setTest($test);
                            $node->setUpdate($update);
                            $node->setBody($body);
                            return $this->completeNode($node);
                        }
                    }
                } else {
                    
                    $this->scanner->setState($state);
                    $left = $this->parseLeftHandSideExpression($yield);
                    $left = $this->expressionToPattern($left);
                    
                    if ($notBeforeSB && $left &&
                        $this->scanner->consume("in")) {
                        
                        if (($right = $this->parseExpression(true, $yield)) &&
                            $this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $node = $this->createNode(
                                "ForInStatement", $token
                            );
                            $node->setLeft($left);
                            $node->setRight($right);
                            $node->setBody($body);
                            return $this->completeNode($node);
                        }
                    } elseif ($left && $this->scanner->consume("of")) {
                        
                        if (($right = $this->parseAssignmentExpression(true, $yield)) &&
                            $this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $node = $this->createNode(
                                "ForOfStatement", $token
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
        return null;
    }
    
    protected function parseFunctionOrGeneratorDeclaration(
        $yield = false, $default = false, $allowGenerator = true)
    {
        if ($token = $this->scanner->consume("function")) {
            
            $generator = $allowGenerator && $this->scanner->consume("*");
            $id = $this->parseIdentifier($yield);
            
            if (($default || $id) &&
                $this->scanner->consume("(") &&
                ($params = $this->parseFormalParameterList($generator)) !== null &&
                $this->scanner->consume(")") &&
                ($tokenBodyStart = $this->scanner->consume("{")) &&
                (($body = $this->parseFunctionBody($generator)) || true) &&
                $this->scanner->consume("}")) {
                
                $body->setStartPosition($tokenBodyStart->getLocation()->getStart());
                $body->setEndPosition($this->scanner->getPosition());
                $node = $this->createNode("FunctionDeclaration", $token);
                if ($id) {
                    $node->setId($id);
                }
                $node->setParams($params);
                $node->setBody($body);
                $node->setGenerator($generator);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseFunctionOrGeneratorExpression()
    {
        if ($token = $this->scanner->consume("function")) {
            
            $generator = $this->scanner->consume("*");
            $id = $this->parseIdentifier(false);
            
            if ($this->scanner->consume("(") &&
                ($params = $this->parseFormalParameterList($generator)) !== null &&
                $this->scanner->consume(")") &&
                ($tokenBodyStart = $this->scanner->consume("{")) &&
                (($body = $this->parseFunctionBody($generator)) || true) &&
                $this->scanner->consume("}")) {
                
                $body->setStartPosition($tokenBodyStart->getLocation()->getStart());
                $body->setEndPosition($this->scanner->getPosition());
                $node = $this->createNode("FunctionExpression", $token);
                $node->setId($id);
                $node->setParams($params);
                $node->setBody($body);
                $node->setGenerator($generator);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseYieldExpression($in = false)
    {
        if ($token = $this->scanner->consume("yield")) {
            
            $node = $this->createNode("YieldExpression", $token);
            if ($this->scanner->noLineTerminators()) {
                
                $delegate = $this->scanner->consume("*");
                if ($argument = $this->parseAssignmentExpression($in, true)) {
                    $node->setArgument($argument);
                    $node->setDelegate($delegate);
                }
            }
            
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseFormalParameterList($yield = false)
    {
        $valid = true;
        $list = array();
        while ($param = $this->parseBindingElement($yield)) {
            $list[] = $param;
            $valid = true;
            if ($this->scanner->consume(",")) {
                if ($restParam = $this->parseBindingRestElement($yield)) {
                    $list[] = $restParam;
                    break;
                }
                $valid = false;
            } else {
                break;
            }
        }
        if (!$valid) {
            return $this->error();
        }
        return $list;
    }
    
    protected function parseFunctionBody($yield = false)
    {
        $body = $this->parseStatementList($yield, true);
        $node = $this->createNode(
            "BlockStatement", $body ? $body : $this->scanner->getPosition()
        );
        if ($body) {
            $node->setBody($body);
        }
        return $this->completeNode($node);
    }
    
    protected function parseClassDeclaration($yield = false, $default = false)
    {
        if ($token = $this->scanner->consume("class")) {
            
            $id = $this->parseIdentifier($yield);
            if (($default || $id) &&
                $tail = $this->parseClassTail($yield)) {
                
                $node = $this->createNode("ClassDeclaration", $token);
                if ($id) {
                    $node->setId($id);
                }
                if ($tail[0]) {
                    $node->setSuperClass($tail[0]);
                }
                $node->setBody($tail[1]);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseClassExpression($yield = false)
    {
        if ($token = $this->scanner->consume("class")) {
            $id = $this->parseIdentifier($yield);
            $tail = $this->parseClassTail($yield);
            $node = $this->createNode("ClassExpression", $token);
            if ($id) {
                $node->setId($id);
            }
            if ($tail[0]) {
                $node->setSuperClass($tail[0]);
            }
            $node->setBody($tail[1]);
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseClassTail($yield = false)
    {
        $heritage = $this->parseClassHeritage($yield);
        if ($token = $this->scanner->consume("{")) {
            
            $body = $this->parseClassBody($yield);
            if ($this->scanner->consume("}")) {
                $body->setStartPosition($token->getLocation()->getStart());
                $body->setEndPosition($this->scanner->getPosition());
                return array($heritage, $body);
            }
        }
        return $this->error();
    }
    
    protected function parseClassHeritage($yield = false)
    {
        if ($this->scanner->consume("extends")) {
            
            if ($superClass = $this->parseLeftHandSideExpression($yield)) {
                return $superClass;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseClassBody($yield = false)
    {
        $body = $this->parseClassElementList($yield);
        $node = $this->createNode(
            "ClassBody", $body ? $body : $this->scanner->getPosition()
        );
        if ($body) {
            $node->setBody($body);
        }
        return $this->completeNode($node);
    }
    
    protected function parseClassElementList($yield = false)
    {
        $items = array();
        while ($item = $this->parseClassElement($yield)) {
            if ($item !== true) {
                $items[] = $item;
            }
        }
        return count($items) ? $items : null;
    }
    
    protected function parseClassElement($yield = false)
    {
        if ($this->scanner->consume(";")) {
            return true;
        }
        
        $staticToken = $this->scanner->consume("static");
        if ($def = $this->parseMethodDefinition($yield)) {
            if ($staticToken) {
                $def->setStatic(true);
                $def->setStartPosition($staticToken->getLocation()->getStart());
            }
            return $def;
        } elseif ($staticToken) {
            return $this->error();
        }
        
        return null;
    }
    
    protected function parseLexicalDeclaration($in = false, $yield = false)
    {
        if ($token = $this->scanner->consumeOneOf(array("let", "const"))) {
            
            $declarations = $this->charSeparatedListOf(
                "parseVariableDeclaration",
                array($in, $yield)
            );
            
            if ($declarations) {
                $this->assertEndOfStatement();
                $node = $this->createNode("VariableDeclaration", $token);
                $node->setKind($token->getValue());
                $node->setDeclarations($declarations);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseVariableStatement($yield = false)
    {
        if ($token = $this->scanner->consume("var")) {
            
            if ($declarations = $this->parseVariableDeclarationList(true, $yield)) {
                $this->assertEndOfStatement();
                $node = $this->createNode("VariableDeclaration", $token);
                $node->setKind($node::KIND_VAR);
                $node->setDeclarations($declarations);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseVariableDeclarationList($in = false, $yield = false)
    {
        return $this->charSeparatedListOf(
            "parseVariableDeclaration",
            array($in, $yield)
        );
    }
    
    protected function parseVariableDeclaration($in = false, $yield = false)
    {
        if ($id = $this->parseIdentifier($yield)) {
            
            $node = $this->createNode("VariableDeclarator", $id);
            $node->setId($id);
            if ($init = $this->parseInitializer($in, $yield)) {
                $node->setInit($init);
            }
            return $this->completeNode($node);
            
        } elseif ($id = $this->parseBindingPattern($yield)) {
            
            if ($init = $this->parseInitializer($in, $yield)) {
                $node = $this->createNode("VariableDeclarator", $id);
                $node->setId($id);
                $node->setInit($init);
                return $this->completeNode($node);
            }
            
        }
        return null;
    }
    
    protected function parseForDeclaration($yield = false)
    {
        if ($token = $this->scanner->consumeOneOf(array("let", "const"))) {
            
            if ($declaration = $this->parseForBinding($yield)) {

                $node = $this->createNode("VariableDeclaration", $token);
                $node->setKind($token->getValue());
                $node->setDeclarations(array($declaration));
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseForBinding($yield = false)
    {
        if (($id = $this->parseIdentifier($yield)) ||
            ($id = $this->parseBindingPattern($yield))) {
            
            $node = $this->createNode("VariableDeclarator", $id);
            $node->setId($id);
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseModuleItem()
    {
        if ($item = $this->parseImportDeclaration()) {
            return $item;
        } elseif ($item = $this->parseExportDeclaration()) {
            return $item;
        } elseif ($item = $this->parseStatementListItem()) {
            return $item;
        }
        return null;
    }
    
    protected function parseFromClause()
    {
        if ($this->scanner->consume("from")) {
            if ($spec = $this->parseStringLiteral()) {
                return $spec;
            }
            return $this->error();
        }
        return null;
    }
    
    protected function parseExportDeclaration()
    {
        if ($token = $this->scanner->consume("export")) {
            
            if ($this->scanner->consume("*")) {
                
                if ($source = $this->parseFromClause()) {
                    $this->assertEndOfStatement();
                    $node = $this->createNode("ExportAllDeclaration", $token);
                    $node->setSource($source);
                    return $this->completeNode($node);
                }
                
            } elseif ($this->scanner->consume("default")) {
                
                if (($declaration = $this->parseFunctionOrGeneratorDeclaration(false, true)) ||
                    ($declaration = $this->parseClassDeclaration(false, true))) {
                    
                    $node = $this->createNode("ExportDefaultDeclaration", $token);
                    $node->setDeclaration($declaration);
                    return $this->completeNode($node);
                    
                } elseif (!$this->scanner->isBefore(array("function", "class")) &&
                          ($declaration = $this->parseAssignmentExpression(true))) {
                    
                    $this->assertEndOfStatement();
                    $node = $this->createNode("ExportDefaultDeclaration", $token);
                    $node->setDeclaration($declaration);
                    return $this->completeNode($node);
                }
                
            } elseif (($specifiers = $this->parseExportClause()) !== null) {
                
                $node = $this->createNode("ExportNamedDeclaration", $token);
                $node->setSpecifiers($specifiers);
                if ($source = $this->parseFromClause()) {
                    $node->setSource($source);
                }
                $this->assertEndOfStatement();
                return $this->completeNode($node);

            } elseif (($dec = $this->parseVariableStatement()) ||
                      $dec = $this->parseDeclaration()) {

                $node = $this->createNode("ExportNamedDeclaration", $token);
                $node->setDeclaration($dec);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseExportClause()
    {
        if ($this->scanner->consume("{")) {
            
            $list = array();
            while ($spec = $this->parseExportSpecifier()) {
                $list[] = $spec;
                if (!$this->scanner->consume(",")) {
                    break;
                }
            }
            
            if ($this->scanner->consume("}")) {
                return $list;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseExportSpecifier()
    {
        if ($local = $this->parseIdentifier()) {
            
            $node = $this->createNode("ExportSpecifier", $local);
            $node->setLocal($local);
            
            if ($this->scanner->consume("as")) {
                
                if ($exported = $this->parseIdentifier()) {
                    $node->setExported($exported);
                    return $this->completeNode($node);
                }
                
                return $this->error();
            } else {
                $node->setExported($local);
                return $this->completeNode($node);
            }
        }
        return null;
    }
    
    protected function parseImportDeclaration()
    {
        if ($token = $this->scanner->consume("import")) {
            
            if ($source = $this->parseStringLiteral()) {
                
                $this->assertEndOfStatement();
                $node = $this->createNode("ImportDeclaration", $token);
                $node->setSource($source);
                return $this->completeNode($node);
                
            } elseif (($specifiers = $this->parseImportClause()) &&
                      $source = $this->parseFromClause()) {
                
                $this->assertEndOfStatement();
                $node = $this->createNode("ImportDeclaration", $token);
                $node->setSpecifiers($specifiers);
                $node->setSource($source);
                
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseImportClause()
    {
        if ($spec = $this->parseNameSpaceImport()) {
            return array($spec);
        } elseif ($specs = $this->parseNamedImports()) {
            return $specs;
        } elseif ($spec = $this->parseIdentifier(false)) {
            
            $node = $this->createNode("ImportDefaultSpecifier", $spec);
            $node->setLocal($spec);
            $ret = array($this->completeNode($node));
            
            if ($this->scanner->consume(",")) {
                
                if ($spec = $this->parseNameSpaceImport()) {
                    $ret[] = $spec;
                    return $ret;
                } elseif ($specs = $this->parseNamedImports()) {
                    $ret = array_merge($ret, $specs);
                    return $ret;
                }
                
                return $this->error();
            } else {
                return $ret;
            }
        }
        return null;
    }
    
    protected function parseNameSpaceImport()
    {
        if ($token = $this->scanner->consume("*")) {
            
            if ($this->scanner->consume("as") &&
                $local = $this->parseIdentifier(false)) {
                $node = $this->createNode("ImportNamespaceSpecifier", $token);
                $node->setLocal($local);
                return $this->completeNode($node);  
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseNamedImports()
    {
        if ($this->scanner->consume("{")) {
            
            $list = array();
            while ($spec = $this->parseImportSpecifier()) {
                $list[] = $spec;
                if (!$this->scanner->consume(",")) {
                    break;
                }
            }
            
            if ($this->scanner->consume("}")) {
                return $list;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseImportSpecifier()
    {
        if ($imported = $this->parseIdentifier()) {
            
            $node = $this->createNode("ImportSpecifier", $imported);
            $node->setImported($imported);
            if ($this->scanner->consume("as")) {
                
                if ($local = $this->parseIdentifier()) {
                    $node->setLocal($local);
                    return $this->completeNode($node);
                }
                
                return $this->error();
            } else {
                $node->setLocal($imported);
                return $this->completeNode($node);
            }
        }
        return null;
    }
    
    protected function parseBindingPattern($yield = false)
    {
        if ($pattern = $this->parseObjectBindingPattern($yield)) {
            return $pattern;
        } elseif ($pattern = $this->parseArrayBindingPattern($yield)) {
            return $pattern;
        }
        return null;
    }
    
    protected function parseElision()
    {
        $count = 0;
        while ($this->scanner->consume(",")) {
            $count ++;
        }
        return $count ? $count : null;
    }
    
    protected function parseArrayBindingPattern($yield = false)
    {
        if ($token = $this->scanner->consume("[")) {
            
            $elements = array();
            while (true) {
                if ($elision = $this->parseElision()) {
                    $elements = array_merge(
                        $elements, array_fill(0, $elision, null)
                    );
                }
                if ($element = $this->parseBindingElement($yield)) {
                    $elements[] = $element;
                    if (!$this->scanner->consume(",")) {
                        break;
                    }
                } elseif ($rest = $this->parseBindingRestElement($yield)) {
                    $elements[] = $rest;
                    break;
                } else {
                    break;
                }
            }
            
            if ($this->scanner->consume("]")) {
                $node = $this->createNode("ArrayPattern", $token);
                $node->setElements($elements);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseBindingRestElement($yield = false)
    {
        if ($token = $this->scanner->consume("...")) {
            
            if ($argument = $this->parseIdentifier($yield)) {
                $node = $this->createNode("RestElement", $token);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseBindingElement($yield = false)
    {
        if ($el = $this->parseSingleNameBinding($yield)) {
            return $el;
        } elseif ($left = $this->parseBindingPattern($yield)) {
            
            if ($right = $this->parseInitializer(true, $yield)) {
                $node = $this->createNode("AssignmentPattern", $left);
                $node->setLeft($left);
                $node->setRight($right);
                return $this->completeNode($node);
            } else {
                return $left;
            }
        }
        return null;
    }
    
    protected function parseSingleNameBinding($yield = false)
    {
        if ($left = $this->parseIdentifier($yield)) {
            if ($right = $this->parseInitializer(true, $yield)) {
                $node = $this->createNode("AssignmentPattern", $left);
                $node->setLeft($left);
                $node->setRight($right);
                return $this->completeNode($node);
            } else {
                return $left;
            }
        }
        return null;
    }
    
    protected function parsePropertyName($yield = false)
    {
        if ($token = $this->scanner->consume("[")) {
            
            if (($name = $this->parseAssignmentExpression(true, $yield)) &&
                $this->scanner->consume("]")) {
                return array($name, true, $token);
            }
            
            return $this->error();
        } elseif ($name = $this->parseIdentifier()) {
            return array($name, false);
        } elseif ($name = $this->parseStringLiteral()) {
            return array($name, false);
        } elseif ($name = $this->parseNumericLiteral()) {
            return array($name, false);
        }
        return null;
    }
    
    protected function parseMethodDefinition($yield = false)
    {
        $state = $this->scanner->getState();
        $generator = false;
        $position = null;
        $error = false;
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
        }
        
        //Handle the case where get and set are methods name and not the
        //definition of a getter/setter
        if ($kind !== Node\MethodDefinition::KIND_METHOD &&
            $this->scanner->consume("(")) {
            $this->scanner->setState($state);
            $kind = Node\MethodDefinition::KIND_METHOD;
            $error = false;
        }
        
        if ($prop = $this->parsePropertyName($yield)) {
            
            if (!$position) {
                $position = isset($prop[2]) ? $prop[2] : $prop[0];
            }
            if ($tokenFn = $this->scanner->consume("(")) {
                
                $error = true;
                $params = array();
                if ($kind === Node\MethodDefinition::KIND_SET) {
                    if ($params = $this->parseBindingElement()) {
                        $params = array($params);
                    }
                } elseif ($kind === Node\MethodDefinition::KIND_METHOD) {
                    $params = $this->parseFormalParameterList();
                }

                if ($params !== null &&
                    $this->scanner->consume(")") &&
                    ($tokenBodyStart = $this->scanner->consume("{")) &&
                    (($body = $this->parseFunctionBody($generator)) || true) &&
                    $this->scanner->consume("}")) {

                    if ($prop[0] instanceof Node\Identifier &&
                        $prop[0]->getName() === "constructor") {
                        $kind = Node\MethodDefinition::KIND_CONSTRUCTOR;
                    }

                    $body->setStartPosition($tokenBodyStart->getLocation()->getStart());
                    $body->setEndPosition($this->scanner->getPosition());
                    
                    $nodeFn = $this->createNode("FunctionExpression", $tokenFn);
                    $nodeFn->setParams($params);
                    $nodeFn->setBody($body);
                    $nodeFn->setGenerator($generator);

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
    
    protected function parseArrowParameters($yield = false)
    {
        if ($param = $this->parseIdentifier($yield)) {
            return $param;
        } elseif ($token = $this->scanner->consume("(")) {
            
            $params = $this->parseFormalParameterList($yield);
            
            if ($params !== null && $this->scanner->consume(")")) {
                return array($params, $token);
            }
        }
        return null;
    }
    
    protected function parseConciseBody($in = false)
    {
        if ($token = $this->scanner->consume("{")) {
            
            if (($body = $this->parseFunctionBody()) &&
                $this->scanner->consume("}")) {
                $body->setStartPosition($token->getLocation()->getStart());
                $body->setEndPosition($this->scanner->getPosition());
                return array($body, false);
            }
            
            return $this->error();
        } elseif (!$this->scanner->isBefore(array("{")) &&
                  $body = $this->parseAssignmentExpression($in)) {
            return array($body, true);
        }
        return null;
    }
    
    protected function parseArrowFunction($in = false, $yield = false)
    {
        $state = $this->scanner->getState();
        if (($params = $this->parseArrowParameters($yield)) !== null) {
            
            if ($this->scanner->noLineTerminators() &&
                $this->scanner->consume("=>")) {
                
                if ($body = $this->parseConciseBody($in)) {
                    if (is_array($params)) {
                        $pos = $params[1];
                        $params = $params[0];
                    } else {
                        $pos = $params;
                        $params = array($params);
                    }
                    $node = $this->createNode("ArrowFunctionExpression", $pos);
                    $node->setParams($params);
                    $node->setBody($body[0]);
                    $node->setExpression($body[1]);
                    return $this->completeNode($node);
                }
            
                return $this->error();
            }
            $this->scanner->setState($state);
        }
        return null;
    }
    
    protected function parseObjectLiteral($yield = false)
    {
        if ($token = $this->scanner->consume("{")) {
            
            $properties = array();
            while ($prop = $this->parsePropertyDefinition($yield)) {
                $properties[] = $prop;
                if (!$this->scanner->consume(",")) {
                    break;
                }
            }
            
            if ($this->scanner->consume("}")) {
                
                $node = $this->createNode("ObjectExpression", $token);
                if ($properties) {
                    $node->setProperties($properties);
                }
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parsePropertyDefinition($yield = false)
    {
        $state = $this->scanner->getState();
        if (($property = $this->parsePropertyName($yield)) &&
             $this->scanner->consume(":")) {

            if ($value = $this->parseAssignmentExpression(true, $yield)) {
                $startPos = isset($property[2]) ? $property[2] : $property[0];
                $node = $this->createNode("Property", $startPos);
                $node->setKey($property[0]);
                $node->setValue($value);
                $node->setComputed($property[1]);
                return $this->completeNode($node);
            }

            return $this->error();
            
        }
        
        $this->scanner->setState($state);
        if ($property = $this->parseMethodDefinition($yield)) {

            $node = $this->createNode("Property", $property);
            $node->setKey($property->getKey());
            $node->setValue($property->getValue());
            $node->setComputed($property->getComputed());
            $kind = $property->getKind();
            if ($kind !== Node\MethodDefinition::KIND_GET &&
                $kind !== Node\MethodDefinition::KIND_SET) {
                $node->setMethod(true);
                $node->setKind(Node\Property::KIND_INIT);
            } else {
                $node->setKind($kind);
            }
            return $this->completeNode($node);
            
        } elseif ($key = $this->parseIdentifier($yield)) {
            
            $node = $this->createNode("Property", $key);
            $node->setShorthand(true);
            $node->setKey($key);
            $node->setValue(
                ($value = $this->parseInitializer(true, $yield)) ?
                $value :
                $key
            );
            return $this->completeNode($node);
            
        }
        return null;
    }
    
    protected function parseInitializer($in = false, $yield = false)
    {
        if ($this->scanner->consume("=")) {
            
            if ($value = $this->parseAssignmentExpression($in, $yield)) {
                return $value;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseObjectBindingPattern($yield = false)
    {
        if ($token = $this->scanner->consume("{")) {
            
            $properties = array();
            while ($prop = $this->parseBindingProperty($yield)) {
                $properties[] = $prop;
                if (!$this->scanner->consume(",")) {
                    break;
                }
            }
            
            if ($this->scanner->consume("}")) {
                $node = $this->createNode("ObjectPattern", $token);
                if ($properties) {
                    $node->setProperties($properties);
                }
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseBindingProperty($yield = false)
    {
        $state = $this->scanner->getState();
        if (($key = $this->parsePropertyName($yield)) &&
            $this->scanner->consume(":")) {
            
            if ($value = $this->parseBindingElement($yield)) {
                $startPos = isset($key[2]) ? $key[2] : $key[0];
                $node = $this->createNode("AssignmentProperty", $startPos);
                $node->setKey($key[0]);
                $node->setComputed($key[1]);
                $node->setValue($value);
                return $this->completeNode($node);
            }
            
            return $this->error();
            
        }
            
        $this->scanner->setState($state);
        if ($property = $this->parseSingleNameBinding($yield)) {
            
            $node = $this->createNode("AssignmentProperty", $property);
            $node->setShorthand(true);
            if ($property instanceof Node\AssignmentPattern) {
                $node->setKey($property->getLeft());
            } else {
                $node->setKey($property);
            }
            $node->setValue($property);
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseExpression($in = false, $yield = false)
    {
        $list = $this->charSeparatedListOf(
            "parseAssignmentExpression",
            array($in, $yield)
        );
        
        if (!$list) {
            return null;
        } elseif (count($list) === 1) {
            return $list[0];
        } else {
            $node = $this->createNode("SequenceExpression", $list);
            $node->setExpressions($list);
            return $this->completeNode($node);
        }
    }
    
    protected function parseAssignmentExpression($in = false, $yield = false)
    {
        $state = $this->scanner->getState();
        $operators = array(
            "=", "+=", "-=", "*=", "/=", "%=", "<<=", ">>=",
            ">>>=", "&=", "^=", "|="
        );
        if ($expr = $this->parseArrowFunction($in, $yield)) {
            return $expr;
        } elseif ($yield && $expr = $this->parseYieldExpression($in)) {
            return $expr;
        } elseif (($left = $this->parseLeftHandSideExpression($yield)) &&
                  $operator = $this->scanner->consumeOneOf($operators)) {
            
            if ($right = $this->parseAssignmentExpression($in, $yield)) {
                
                $node = $this->createNode("AssignmentExpression", $left);
                $node->setLeft($this->expressionToPattern($left));
                $node->setOperator($operator->getValue());
                $node->setRight($right);
                return $this->completeNode($node);
                
            }
            return $this->error();
        }
        $this->scanner->setState($state);
        if ($expr = $this->parseConditionalExpression($in, $yield)) {
            return $expr;
        }
        return null;
    }
    
    protected function parseConditionalExpression($in = false, $yield = false)
    {
        if ($test = $this->parseLogicalBinaryExpression($in, $yield)) {
            
            if ($this->scanner->consume("?")) {
                
                if (($consequent = $this->parseAssignmentExpression($in, $yield)) &&
                    $this->scanner->consume(":") &&
                    $alternate = $this->parseAssignmentExpression($in, $yield)) {
                
                    $node = $this->createNode("ConditionalExpression", $test);
                    $node->setTest($test);
                    $node->setConsequent($consequent);
                    $node->setAlternate($alternate);
                    return $this->completeNode($node);
                }
                
                return $this->error();
            } else {
                return $test;
            }
        }
        return null;
    }
    
    protected function parseLogicalBinaryExpression($in = false, $yield = false)
    {
        $operators = array(
            "||" => 0,
            "&&" => 1,
            "|" => 2,
            "^" => 3,
            "&" => 4,
            "===" => 5, "!==" => 5, "==" => 5, "!=" => 5,
            "<=" => 6, ">=" => 6, "<" => 6, ">" => 6, "instanceof" => 6, "in" => 6,
            ">>>" => 7, "<<" => 7, ">>" => 7,
            "+" => 8, "-" => 8,
            "*" => 9, "/" => 9, "%" => 9
        );
        if (!$in) {
            unset($operators["in"]);
        }
        
        if (!($exp = $this->parseUnaryExpression($yield))) {
            return null;
        }
        
        $list = array($exp);
        while ($token = $this->scanner->consumeOneOf(array_keys($operators))) {
            if (!($exp = $this->parseUnaryExpression($yield))) {
                return $this->error();
            }
            $list[] = $token->getValue();
            $list[] = $exp;
        }
        
        $len = count($list);
        if ($len > 1) {
            $maxGrade = max($operators);
            for ($grade = $maxGrade; $grade >= 0; $grade--) {
                $class = $grade < 2 ? "LogicalExpression" : "BinaryExpression";
                for ($i = 1; $i < $len; $i += 2) {
                    if ($operators[$list[$i]] === $grade) {
                        $node = $this->createNode($class, $list[$i - 1]);
                        $node->setLeft($list[$i - 1]);
                        $node->setOperator($list[$i]);
                        $node->setRight($list[$i + 1]);
                        $node = $this->completeNode(
                            $node, $list[$i + 1]->getLocation()->getEnd()
                        );
                        array_splice($list, $i - 1, 3, array($node));
                        $i -= 2;
                        $len = count($list);
                    }
                }
            }
        }
        return $list[0];
    }
    
    protected function parseUnaryExpression($yield = false)
    {
        if ($expr = $this->parsePostfixExpression($yield)) {
            return $expr;
        } elseif ($token = $this->scanner->consumeOneOf(array(
                    "delete", "void", "typeof", "++", "--", "+", "-", "~", "!"
                  ))) {
            if ($argument = $this->parseUnaryExpression($yield)) {
                if ($token->getValue() === "++" || $token->getValue() === "--") {
                    $node = $this->createNode("UpdateExpression", $token);
                    $node->setPrefix(true);
                } else {
                    $node = $this->createNode("UnaryExpression", $token);
                }
                $node->setOperator($token->getValue());
                $node->setArgument($argument);
                return $this->completeNode($node);
            }

            return $this->error();
        }
        return null;
    }
    
    protected function parsePostfixExpression($yield = false)
    {
        if ($argument = $this->parseLeftHandSideExpression($yield)) {
            
            if ($this->scanner->noLineTerminators() &&
                $token = $this->scanner->consumeOneOf(array("--", "++"))) {
                
                $node = $this->createNode("UpdateExpression", $argument);
                $node->setOperator($token->getValue());
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $argument;
        }
        return null;
    }
    
    protected function parseLeftHandSideExpression($yield = false)
    {
        if ($expr = $this->parseCallExpression($yield)) {
            return $expr;
        } elseif ($expr = $this->parseNewExpression($yield)) {
            return $expr;
        }
        return null;
    }
    
    protected function parseSpreadElement($yield = false)
    {
        if ($token = $this->scanner->consume("...")) {
            
            if ($argument = $this->parseAssignmentExpression(true, $yield)) {
                $node = $this->createNode("SpreadElement", $token);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseArrayLiteral($yield = false)
    {
        if ($token = $this->scanner->consume("[")) {
            
            $elements = array();
            while (true) {
                if ($elision = $this->parseElision()) {
                    $elements = array_merge(
                        $elements, array_fill(0, $elision, null)
                    );
                }
                if (($element = $this->parseSpreadElement($yield)) ||
                    ($element = $this->parseAssignmentExpression(true, $yield))) {
                    $elements[] = $element;
                    if (!$this->scanner->consume(",")) {
                        break;
                    }
                } else {
                    break;
                }
            }
            
            if ($this->scanner->consume("]")) {
                $node = $this->createNode("ArrayExpression", $token);
                $node->setElements($elements);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseArguments($yield = false)
    {
        if ($this->scanner->consume("(")) {
            
            if (($args = $this->parseArgumentList($yield)) !== null &&
                $this->scanner->consume(")")) {
                return $args;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseArgumentList($yield = false)
    {
        $list = array();
        $start = $valid = true;
        while (true) {
            $spread = $this->scanner->consume("...");
            $exp = $this->parseAssignmentExpression(true, $yield);
            if (!$exp) {
                $valid = $valid && $start && !$spread;
                break;
            }
            if ($spread) {
                $node = $this->createNode("SpreadElement", $spread);
                $node->setArgument($exp);
                $list[] = $this->completeNode($node);
            } else {
                $list[] = $exp;
            }
            $valid = true;
            if (!$this->scanner->consume(",")) {
                break;
            } else {
                $valid = false;
            }
        }
        $start = false;
        if (!$valid) {
            return $this->error();
        }
        return $list;
    }
    

    protected function parseSuperCall($yield = false)
    {
        if ($this->scanner->isBefore(array(array("super", "(")), true)) {
            
            $state = $this->scanner->getState();
            $token = $this->scanner->consume("super");
            $endPos = $this->scanner->getPosition();
            $args = $this->parseArguments($yield);
            if ($args !== null) {
                
                $super = $this->createNode("Super", $token);
                $node = $this->createNode("CallExpression", $token);
                $node->setArguments($args);
                $node->setCallee($this->completeNode($super, $endPos));
                return $this->completeNode($node);
            }
            $this->scanner->setState($state);
        }
        return null;
    }
    
    protected function parseMemberExpression($yield = false)
    {
        $state = $this->scanner->getState();
        if ($newToken = $this->scanner->consume("new")) {
            
            if ($this->scanner->consume(".")) {
                
                if ($this->scanner->consume("target")) {
                    
                    $node = $this->createNode("MetaProperty", $newToken);
                    $node->setMeta("new");
                    $node->setProperty("target");
                    $object = $this->completeNode($node);
                    
                } else {
                    return $this->error();
                }
                
            } elseif (($callee = $this->parseMemberExpression($yield)) &&
                      ($args = $this->parseArguments($yield)) !== null) {
                
                $node = $this->createNode("NewExpression", $newToken);
                $node->setCallee($callee);
                $node->setArguments($args);
                $object = $this->completeNode($node);
                
            } else {
                $this->scanner->setState($state);
                return null;
            }
            
        } elseif (!($object = $this->parseSuperProperty($yield)) &&
                  !($object = $this->parsePrimaryExpression($yield))) {
            return null;
        }
        
        $valid = true;
        $properties = array();
        while (true) {
            if ($this->scanner->consume(".")) {
                if ($property = $this->parseIdentifier()) {
                    $properties[] = array($property);
                } else {
                    $valid = false;
                    break;
                }
            } elseif ($this->scanner->consume("[")) {
                if (($property = $this->parseExpression(true, $yield)) &&
                    $this->scanner->consume("]")) {
                    $properties[] = array(
                        $property,
                        $this->scanner->getPosition()
                    );
                } else {
                    $valid = false;
                    break;
                }
            } elseif ($property = $this->parseTemplateLiteral($yield)) {
                $properties[] = $property;
            } else {
                break;
            }
        }
        
        if (!$valid) {
            return $this->error();
        } elseif (!count($properties)) {
            return $object;
        }
        
        $lastIndex = count($properties) - 1;
        $node = $this->createNode("MemberExpression", $object);
        $node->setObject($object);
        $endPos = $object->getLocation()->getEnd();
        foreach ($properties as $i => $property) {
            if (is_array($property)) {
                $node->setProperty($property[0]);
                $endPos = $property[0]->getLocation()->getEnd();
                if (isset($property[1])) {
                    $node->setComputed(true);
                    $endPos = $property[1];
                }
            } else {
                $lastNode = $node->getObject();
                $node = $this->createNode("TaggedTemplateExpression", $object);
                $node->setTag($this->completeNode($lastNode, $endPos));
                $node->setQuasi($property);
                $endPos = $property->getLocation()->getEnd();
            }
            if ($i !== $lastIndex) {
                $lastNode = $node;
                $node = $this->createNode("MemberExpression", $object);
                $node->setObject($this->completeNode($lastNode, $endPos));
            }
        }
        
        return $this->completeNode($node);
    }
    
    protected function parseSuperProperty($yield = false)
    {
        if ($token = $this->scanner->consume("super")) {
            
            $super = $this->createNode("Super", $token);
            
            $node = $this->createNode("MemberExpression", $token);
            $node->setObject($this->completeNode($super));
            
            if ($this->scanner->consume(".")) {
                
                if ($property = $this->parseIdentifier()) {
                    $node->setProperty($property);
                    return $this->completeNode($node);
                }
            } elseif ($this->scanner->consume("[") &&
                      ($property = $this->parseExpression(true, $yield)) &&
                      $this->scanner->consume("]")) {
                
                $node->setProperty($property);
                $node->setComputed(true);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseNewExpression($yield = false)
    {
        if ($token = $this->scanner->consume("new")) {
            
            if (($callee = $this->parseMemberExpression($yield)) ||
                $callee = $this->parseNewExpression($yield)) {
                $node = $this->createNode("NewExpression", $token);
                $node->setCallee($callee);
                return $this->completeNode($node);
            }
            
            return $this->error();
        } elseif ($callee = $this->parseMemberExpression($yield)) {
            return $callee;
        }
        return null;
    }
    
    protected function parsePrimaryExpression($yield = false)
    {
        if ($token = $this->scanner->consume("this")) {
            $node = $this->createNode("ThisExpression", $token);
            return $this->completeNode($node);
        } elseif ($exp = $this->parseIdentifier($yield)) {
            return $exp;
        } elseif ($exp = $this->parseLiteral()) {
            return $exp;
        } elseif ($exp = $this->parseArrayLiteral($yield)) {
            return $exp;
        } elseif ($exp = $this->parseObjectLiteral($yield)) {
            return $exp;
        } elseif ($exp = $this->parseFunctionOrGeneratorExpression()) {
            return $exp;
        } elseif ($exp = $this->parseClassExpression($yield)) {
            return $exp;
        } elseif ($exp = $this->parseRegularExpressionLiteral()) {
            return $exp;
        } elseif ($exp = $this->parseTemplateLiteral($yield)) {
            return $exp;
        } elseif ($token = $this->scanner->consume("(")) {
            
            if (($exp = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")")) {
                
                $node = $this->createNode("ParenthesizedExpression", $token);
                $node->setExpression($exp);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseIdentifier($disallowYield = null)
    {
        $token = $this->scanner->getToken();
        if (!$token) {
            return null;
        }
        $type = $token->getType();
        switch ($type) {
            case Token::TYPE_KEYWORD:
            case Token::TYPE_BOOLEAN_LITERAL:
            case Token::TYPE_NULL_LITERAL:
                if ($disallowYield !== null &&
                    ($disallowYield || $token->getValue() !== "yield")) {
                    return null;
                }
            break;
            default:
                if ($type !== Token::TYPE_IDENTIFIER) {
                    return null;
                }
            break;
        }
        $this->scanner->consumeToken();
        $node = $this->createNode("Identifier", $token);
        $node->setName($token->getValue());
        return $this->completeNode($node);
    }
    
    protected function parseCallExpression($yield = false)
    {
        $state = $this->scanner->getState();
        $object = $this->parseSuperCall($yield);
        if (!$object) {
            
            $callee = $this->parseMemberExpression($yield);
            $args = $callee ? $this->parseArguments($yield) : null;
            
            if ($callee === null || $args === null) {
                if ($callee !== null && $callee instanceof Node\NewExpression) {
                    return $callee;
                }
                $this->scanner->setState($state);
                return null;
            }
            
            $object = $this->createNode("CallExpression", $callee);
            $object->setCallee($callee);
            $object->setArguments($args);
            $object = $this->completeNode($object);
        }
        
        $valid = true;
        $properties = array();
        while (true) {
            if (($args = $this->parseArguments($yield)) !== null) {
                $properties[] = array(
                    $args,
                    $this->scanner->getPosition()
                );
            } elseif ($this->scanner->consume(".")) {
                if ($property = $this->parseIdentifier()) {
                    $properties[] = array($property);
                } else {
                    $valid = false;
                    break;
                }
            } elseif ($this->scanner->consume("[")) {
                if (($property = $this->parseExpression(true, $yield)) &&
                    $this->scanner->consume("]")) {
                    $properties[] = array(
                        $property,
                        $this->scanner->getPosition()
                    );
                } else {
                    $valid = false;
                    break;
                }
            } elseif ($property = $this->parseTemplateLiteral($yield)) {
                $properties[] = $property;
            } else {
                break;
            }
        }
        
        if (!$valid) {
            return $this->error();
        } elseif (!count($properties)) {
            return $object;
        }
        
        $node = $object;
        $endPos = $object->getLocation()->getEnd();
        foreach ($properties as $property) {
            if (is_array($property)) {
                if (is_array($property[0])) {
                    $lastNode = $node;
                    $node = $this->createNode("CallExpression", $object);
                    $node->setCallee($this->completeNode($lastNode, $endPos));
                    $node->setArguments($property[0]);
                    $endPos = $property[1];
                } else {
                    $lastNode = $node;
                    $node = $this->createNode("MemberExpression", $object);
                    $node->setObject($this->completeNode($lastNode, $endPos));
                    $node->setProperty($property[0]);
                    $endPos = $property[0]->getLocation()->getEnd();
                    if (isset($property[1])) {
                        $node->setComputed(true);
                        $endPos = $property[1];
                    }
                }
            } else {
                $lastNode = $node;
                $node = $this->createNode("TaggedTemplateExpression", $object);
                $node->setTag($this->completeNode($lastNode, $endPos));
                $node->setQuasi($property);
                $endPos = $property->getLocation()->getEnd();
            }
        }
        
        return $this->completeNode($node);
    }
    
    protected function parseLiteral()
    {
        $token = $this->scanner->getToken();
        if ($token && ($token->getType() === Token::TYPE_NULL_LITERAL ||
            $token->getType() === Token::TYPE_BOOLEAN_LITERAL)) {
            $this->scanner->consumeToken();
            $node = $this->createNode("Literal", $token);
            $node->setRaw($token->getValue());
            return $this->completeNode($node);
        } elseif ($literal = $this->parseStringLiteral()) {
            return $literal;
        } elseif ($literal = $this->parseNumericLiteral()) {
            return $literal;
        }
        return null;
    }
    
    protected function parseStringLiteral()
    {
        $token = $this->scanner->getToken();
        if ($token && $token->getType() === Token::TYPE_STRING_LITERAL) {
            $this->scanner->consumeToken();
            $node = $this->createNode("Literal", $token);
            $node->setRaw($token->getValue());
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseNumericLiteral()
    {
        $token = $this->scanner->getToken();
        if ($token && $token->getType() === Token::TYPE_NUMERIC_LITERAL) {
            $this->scanner->consumeToken();
            $node = $this->createNode("Literal", $token);
            $node->setRaw($token->getValue());
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseTemplateLiteral($yield = false)
    {
        $token = $this->scanner->getToken();
        
        if (!$token || $token->getType() !== Token::TYPE_TEMPLATE) {
            return null;
        }
        
        //Do not parse templates parts
        $val = $token->getValue();
        if ($val[0] !== "`") {
            return null;
        }
        
        $quasis = $expressions = array();
        $valid = false;
        do {
            $this->scanner->consumeToken();
            $val = $token->getValue();
            $lastChar = substr($val, -1);
            
            $quasi = $this->createNode("TemplateElement", $token);
            $quasi->setRawValue($val);
            if ($lastChar === "`") {
                $quasi->setTail(true);
                $quasis[] = $this->completeNode($quasi);
                $valid = true;
                break;
            } else {
                $quasis[] = $this->completeNode($quasi);
                if ($exp = $this->parseExpression(true, $yield)) {
                    $expressions[] = $exp;
                } else {
                    $valid = false;
                    break;
                }
            }
            
            $token = $this->scanner->getToken();
        } while ($token && $token->getType() === Token::TYPE_TEMPLATE);
        
        if ($valid) {
            $node = $this->createNode("TemplateLiteral", $quasis[0]);
            $node->setQuasis($quasis);
            $node->setExpressions($expressions);
            return $this->completeNode($node);
        }
        
        return $this->error();
    }
    
    protected function parseRegularExpressionLiteral()
    {
        if ($token = $this->scanner->reconsumeCurrentTokenAsRegexp()) {
            $this->scanner->consumeToken();
            $node = $this->createNode("RegExpLiteral", $token);
            $node->setRaw($token->getValue());
            return $this->completeNode($node);
        }
        return null;
    }
}