<?php
namespace Peast\Syntax\ES6;

class Parser extends \Peast\Syntax\Parser
{
    protected $moduleMode = false;
    
    protected $config;
    
    public function __construct($module = false)
    {
        $this->config = self::getConfig();
        $this->moduleMode = $module;
    }
    
    static public function getConfig()
    {
        return Config::getInstance();
    }
    
    public function setScanner(\Peast\Syntax\Scanner $scanner)
    {
        $scanner->setConfig($this->config);
        return parent::setScanner($scanner);
    }
    
    public function parse()
    {
        if ($this->moduleMode) {
            return $this->parseModule();
        } else {
            return $this->parseScript();
        }
    }
    
    protected function parseScript()
    {
        $body = $this->parseStatementList();
        $node = $this->createNode(
            "Program", $body ? $body : $this->scanner->getPosition()
        );
        $node->setSourceType($node::SOURCE_TYPE_SCRIPT);
        if ($body) {
            $node->setBody($body);
        }
        return $this->completeNode($node);
    }
    
    protected function parseModule()
    {
        $body = $this->parseModuleItemList();
        $node = $this->createNode(
            "Program", $body ? $body : $this->scanner->getPosition()
        );
        $node->setSourceType($node::SOURCE_TYPE_SCRIPT);
        if ($body) {
            $node->setBody($body);
        }
        return $this->completeNode($node);
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
        if ($statement = $this->parseStatement($yield, $return)) {
            return $statement;
        } elseif ($declaration = $this->parseDeclaration($yield)) {
            return $declaration;
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
        } elseif ($statement = $this->parseExpressionStatement($yield)) {
            return $statement;
        } elseif ($statement = $this->parseLabelledStatement($yield, $return)) {
            return $statement;
        }
        return null;
    }
    
    protected function parseDeclaration($yield = false)
    {
        if ($declaration = $this->parseHoistableDeclaration($yield)) {
            return $declaration;
        } elseif ($declaration = $this->parseClassDeclaration($yield)) {
            return $declaration;
        } elseif ($declaration = $this->parseLexicalDeclaration(true, $yield)) {
            return $declaration;
        }
        return null;
    }
    
    protected function parseHoistableDeclaration($yield = false, $default = false)
    {
        if ($declaration = $this->parseFunctionDeclaration($yield, $default)) {
            return $declaration;
        } elseif ($declaration = $this->parseGeneratorDeclaration($yield, $default)) {
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
        if ($this->scanner->consume("{")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $statements = $this->parseStatementList($yield, $return);
            if ($this->scanner->consume("}")) {
                $node = $this->createNode("BlockStatement", $position);
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
        if ($this->scanner->consume(";")) {
            $node = $this->createNode(
                "EmptyStatement", $this->scanner->getConsumedTokenPosition()
            );
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseDebuggerStatement()
    {
        if ($this->scanner->consume("debugger")) {
            $node = $this->createNode(
                "DebuggerStatement", $this->scanner->getConsumedTokenPosition()
            );
            $this->scanner->consume(";");
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseIfStatement($yield = false, $return = false)
    {
        if ($this->scanner->consume("if")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consume("(") &&
                ($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                $consequent = $this->parseStatement($yield, $return)) {
                
                $node = $this->createNode("IfStatement", $position);
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
        if ($this->scanner->consume("try")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($block = $this->parseBlock($yield, $return)) {
                
                $node = $this->createNode("TryStatement", $position);
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
        if ($this->scanner->consume("catch")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consume("(") &&
                ($param = $this->parseCatchParameter($yield)) &&
                $this->scanner->consume(")") &&
                $body = $this->parseBlock($yield, $return)) {

                $node = $this->createNode("CatchClause", $position);
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
        if ($param = $this->parseIdentifierReference($yield)) {
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
        if ($this->scanner->consume("continue")) {
            
            $node = $this->createNode(
                "ContinueStatement", $this->scanner->getConsumedTokenPosition()
            );
            
            if ($this->scanner->consumeWhitespacesAndComments(false)) {
                
                if ($label = $this->parseIdentifierReference($yield)) {
                    $node->setLabel($label);
                } elseif (!$this->scanner->consume(";")) {
                    return $this->error();
                }
                
            } else {
                $this->scanner->consume(";");
            }
            
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseBreakStatement($yield = false)
    {
        if ($this->scanner->consume("break")) {
            
            $node = $this->createNode(
                "BreakStatement", $this->scanner->getConsumedTokenPosition()
            );
            
            if ($this->scanner->consumeWhitespacesAndComments(false)) {
                
                if ($label = $this->parseIdentifierReference($yield)) {
                    $node->setLabel($label);
                } elseif (!$this->scanner->consume(";")) {
                    return $this->error();
                }
                
            } else {
                $this->scanner->consume(";");
            }
            
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseReturnStatement($yield = false)
    {
        if ($this->scanner->consume("return")) {
            
            $node = $this->createNode(
                "ReturnStatement", $this->scanner->getConsumedTokenPosition()
            );
            
            if ($this->scanner->consumeWhitespacesAndComments(false)) {
                
                if ($argument = $this->parseExpression(true, $yield)) {
                    $node->setArgument($argument);
                } elseif (!$this->scanner->consume(";")) {
                    return $this->error();
                }
                
            } else {
                $this->scanner->consume(";");
            }
            
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseLabelledStatement($yield = false, $return = false)
    {
        $position = $this->scanner->getPosition();
        if ($label = $this->parseIdentifierReference($yield)) {
            
            if ($this->scanner->consume(":")) {
                
                if ($body = $this->parseLabelledItem($yield, $return)) {
                    
                    $node = $this->createNode("LabeledStatement", $label);
                    $node->setLabel($label);
                    $node->setBody($body);
                    return $this->completeNode($node);
                    
                }
                
                return $this->error();
            }
            
            $this->scanner->setPosition($position);
        }
        return null;
    }
    
    protected function parseLabelledItem($yield = false, $return = false)
    {
        if ($statement = $this->parseStatement($yield, $return)) {
            return $statement;
        } elseif ($function = $this->parseFunctionDeclaration($yield)) {
            return $function;
        }
        return null;
    }
    
    protected function parseThrowStatement($yield = false)
    {
        if ($this->scanner->consume("throw")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consumeWhitespacesAndComments(false) &&
                ($argument = $this->parseExpression(true, $yield))) {
                
                $this->scanner->consume(";");
                $node = $this->createNode("ThrowStatement", $position);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseWithStatement($yield = false, $return = false)
    {
        if ($this->scanner->consume("with")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consume("(") &&
                ($object = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                $body = $this->parseStatement($yield, $return)) {
            
                $node = $this->createNode("WithStatement", $position);
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
        if ($this->scanner->consume("switch")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consume("(") &&
                ($discriminant = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                ($cases = $this->parseCaseBlock($yield, $return)) !== null) {
            
                $node = $this->createNode("SwitchStatement", $position);
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
                $this->error("Multiple default clause in switch statement");
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
        if ($this->scanner->consume("case")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if (($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(":")) {

                $node = $this->createNode("SwitchCase", $position);
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
        if ($this->scanner->consume("default")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consume(":")) {

                $node = $this->createNode("SwitchCase", $position);
            
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
        if ($this->scanner->notBefore($lookahead) &&
            $expression = $this->parseExpression(true, $yield)) {
            
            $this->scanner->consume(";");
            $node = $this->createNode("ExpressionStatement", $expression);
            $node->setExpression($expression);
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseIterationStatement($yield = false, $return = false)
    {
        if ($this->scanner->consume("do")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if (($body = $this->parseStatement($yield, $return)) &&
                $this->scanner->consumeArray(array("while", "(")) &&
                ($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")")) {
                    
                $node = $this->createNode("DoWhileStatement", $position);
                $node->setBody($body);
                $node->setTest($test);
                return $this->completeNode($node);
            }
            return $this->error();
            
        } elseif ($this->scanner->consume("while")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consume("(") &&
                ($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                $body = $this->parseStatement($yield, $return)) {
                    
                $node = $this->createNode("WhileStatement", $position);
                $node->setTest($test);
                $node->setBody($body);
                return $this->completeNode($node);
            }
            return $this->error();
            
        } elseif ($this->scanner->consume("for")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $hasBracket = $this->consume("(");
            
            if (!$hasBracket) {
                return $this->error();
            } elseif ($this->scanner->consume("var")) {
                
                $subPosition = $this->scanner->getConsumedTokenPosition();
                
                if (($decl = $this->parseVariableDeclarationList($yield)) &&
                    $this->scanner->consume(";")) {
                    
                    $test = $this->parseExpression(true, $yield);
                    
                    if ($this->scanner->consume(";")) {
                        
                        $update = $this->parseExpression(true, $yield);
                        
                        if ($this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $init = $this->createNode(
                                "VariableDeclaration", $subPosition
                            );
                            $init->setKind($init::KIND_VAR);
                            $init->setDeclarations($decl);
                            $init = $this->completeNode($init);
                            
                            $node = $this->createNode(
                                "ForStatement", $position
                            );
                            $node->setInit($init);
                            $node->setTest($test);
                            $node->setUpdate($update);
                            $node->setBody($body);
                            return $this->completeNode($node);
                        }
                    }
                } else {
                    
                    $this->scanner->setPosition($subPosition);
                    
                    if ($decl = $this->parseForBinding($yield)) {
                        
                        $left = $this->createNode(
                            "VariableDeclaration", $subPosition
                        );
                        $left->setKind($left::KIND_VAR);
                        $left->setDeclarations(array($decl));
                        $left = $this->completeNode($left);
                        
                        if ($this->scanner->consume("in")) {
                            
                            if (($right = $this->parseExpression(true, $yield)) &&
                                $this->scanner->consume(")") &&
                                $body = $this->parseStatement($yield, $return)) {
                                
                                $node = $this->createNode(
                                    "ForInStatement", $position
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
                                    "ForOfStatement", $position
                                );
                                $node->setLeft($left);
                                $node->setRight($right);
                                $node->setBody($body);
                                return $this->completeNode($node);
                            }
                        }
                    }
                }
            } elseif ($init = $this->parseLexicalDeclaration($yield)) {
                
                $test = $this->parseExpression(true, $yield);
                
                if ($this->scanner->consume(";")) {
                        
                    $update = $this->parseExpression(true, $yield);
                    
                    if ($this->scanner->consume(")") &&
                        $body = $this->parseStatement($yield, $return)) {
                        
                        $node = $this->createNode("ForStatement", $position);
                        $node->setInit($init);
                        $node->setTest($test);
                        $node->setUpdate($update);
                        $node->setBody($body);
                        return $this->completeNode($node);
                    }
                }
            } elseif ($left = $this->parseForDeclaration($yield)) {
                
                if ($this->scanner->consume("in")) {
                            
                    if (($right = $this->parseExpression(true, $yield)) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement($yield, $return)) {
                        
                        $node = $this->createNode("ForInStatement", $position);
                        $node->setLeft($left);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                    }
                } elseif ($this->scanner->consume("of")) {
                    
                    if (($right = $this->parseAssignmentExpression(true, $yield)) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement($yield, $return)) {
                        
                        $node = $this->createNode("ForOfStatement", $position);
                        $node->setLeft($left);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                    }
                }
            } elseif ($this->scanner->notBefore(array("let"))) {
                
                $subPosition = $this->scanner->getPosition();
                $notBeforeSB = $this->scanner->notBefore(array("let", "["));
                
                if ($notBeforeSB &&
                    (($init = $this->parseExpression(false, $yield)) || true) &&
                    $this->scanner->consume(";")) {
                
                    $test = $this->parseExpression(true, $yield);
                    
                    if ($this->scanner->consume(";")) {
                            
                        $update = $this->parseExpression(true, $yield);
                        
                        if ($this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $node = $this->createNode(
                                "ForStatement", $position
                            );
                            $node->setInit($init);
                            $node->setTest($test);
                            $node->setUpdate($update);
                            $node->setBody($body);
                            return $this->completeNode($node);
                        }
                    }
                } else {
                    
                    $this->scanner->setPosition($subPosition);
                    $left = $this->parseLeftHandSideExpression($yield);
                    
                    if ($notBeforeSB && $left &&
                        $this->scanner->consume("in")) {
                        
                        if (($right = $this->parseExpression(true, $yield)) &&
                            $this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $node = $this->createNode(
                                "ForInStatement", $position
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
                                "ForOfStatement", $position
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
    
    protected function parseFunctionDeclaration($yield = false, $default = false)
    {
        if ($this->scanner->consume("function")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $id = $this->parseIdentifierReference($yield);
            
            if (($default || $id) &&
                $this->scanner->consume("(") &&
                ($params = $this->parseFormalParameterList()) !== null &&
                $this->scanner->consumeArray(array(")", "{")) &&
                (($body = $this->parseFunctionBody()) || true) &&
                $this->scanner->consume("}")) {
                
                $node = $this->createNode("FunctionDeclaration", $position);
                if ($id) {
                    $node->setId($id);
                }
                $node->setParams($params);
                $node->setBody($body);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseGeneratorDeclaration($yield = false, $default = false)
    {
        if ($this->scanner->consumeArray(array("function", "*"))) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $id = $this->parseBindingIdentifier($yield);
            
            if (($default || $id) &&
                $this->scanner->consume("(") &&
                ($params = $this->parseFormalParameters(true)) !== null  &&
                $this->scanner->consumeArray(array(")", "{")) &&
                (($body = $this->parseFunctionBody(true)) || true) &&
                $this->scanner->consume("}")) {
                
                $node = $this->createNode("FunctionDeclaration", $position);
                if ($id) {
                    $node->setId($id);
                }
                $node->setParams($params);
                $node->setBody($body);
                $node->setGenerator(true);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseFunctionExpression()
    {
        if ($this->scanner->consume("function")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $id = $this->parseIdentifierReference();
            
            if ($this->scanner->consume("(") &&
                ($params = $this->parseFormalParameters()) !== null &&
                $this->scanner->consumeArray(array(")", "{")) &&
                (($body = $this->parseFunctionBody()) || true) &&
                $this->scanner->consume("}")) {
                
                $node = $this->createNode("FunctionExpression", $position);
                $node->setId($id);
                $node->setParams($params);
                $node->setBody($body);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseGeneratorExpression()
    {
        if ($this->scanner->consumeArray(array("function", "*"))) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $id = $this->parseBindingIdentifier(true);
            
            if ($this->scanner->consume("(") &&
                ($params = $this->parseFormalParameters(true)) !== null &&
                $this->scanner->consumeArray(array(")", "{")) &&
                (($body = $this->parseFunctionBody(true)) || true) &&
                $this->scanner->consume("}")) {
                
                $node = $this->createNode("FunctionExpression", $position);
                $node->setId($id);
                $node->setParams($params);
                $node->setBody($body);
                $node->setGenerator(true);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseYieldExpression($in = false)
    {
        if ($this->scanner->consume("yield")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $node = $this->createNode("YieldExpression", $position);
            if ($this->scanner->consumeWhitespacesAndComments(false)) {
                $delegate = $this->scanner->consume("*") ? true : false;
                if ($argument = $this->parseAssignmentExpression($in, true)) {
                    
                    $node->setArgument($argument);
                    $node->setDelegate($delegate);
                    return $this->completeNode($node);
                }
            } else {
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseFormalParameterList($yield = false)
    {
        $list = array();
        $rest = true;
        $restMandatory = false;
        while ($param = $this->parseBindingElement($yield)) {
            $list[] = $param;
            $rest = false;
            if ($this->scanner->consume(",")) {
                $rest = true;
                $restMandatory = true;
            } else {
                break;
            }
        }
        if ($rest) {
            if ($restParam = $this->parseBindingRestElement($yield)) {
                $list[] = $restParam;
            } elseif ($restMandatory) {
                return $this->error();
            }
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
        if ($this->scanner->consume("class")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $id = $this->parseIdentifierReference($yield);
            
            if (($default || $id) &&
                $tail = $this->parseClassTail($yield)) {
                
                $node = $this->createNode("ClassDeclaration", $position);
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
        if ($this->scanner->consume("class")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $id = $this->BindingIdentifier($yield);
            
            if ($tail = $this->parseClassTail($yield)) {
                
                $node = $this->createNode("ClassExpression", $position);
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
    
    protected function parseClassTail($yield = false)
    {
        $heritage = $this->parseClassHeritage($yield);
        if ($this->scanner->consume("{")) {
            
            $body = $this->parseClassBody($yield);
            if ($this->scanner->consume("}")) {
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
        } elseif ($def = $this->parseMethodDefinition($yield)) {
            return $def;
        }
        
        if ($this->scanner->consume("static")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($def = $this->parseMethodDefinition($yield)) {
                $def->setStatic(true);
                $def->setStartPosition($position);
                return $def;        
            }

            return $this->error();
        }
        return null;
    }
    
    protected function parseLexicalDeclaration($in = false, $yield = false)
    {
        if ($letOrConst = $this->scanner->consumeOneOf(array("let", "const"))) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $declarations = $this->charSeparatedListOf(
                "parseVariableDeclaration",
                array($in, $yield)
            );
            
            if ($declarations) {
                $this->scanner->consume(";");
                $node = $this->createNode("VariableDeclaration", $position);
                $node->setKind($letOrConst);
                $node->setDeclarations($declarations);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseVariableStatement($yield = false)
    {
        if ($this->scanner->consume("var")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $declarations = $this->parseVariableDeclarationList(true, $yield);
            
            if ($declarations) {
                $this->scanner->consume(";");
                $node = $this->createNode("VariableDeclaration", $position);
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
        if ($id = $this->parseIdentifierReference($yield)) {
            
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
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseForDeclaration($yield = false)
    {
        if ($letOrConst = $this->scanner->consumeOneOf(array("let", "const"))) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($declaration = $this->parseForBinding($yield)) {

                $node = $this->createNode("VariableDeclaration", $position);
                $node->setKind($letOrConst);
                $node->setDeclarations(array($declaration));
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseForBinding($yield = false)
    {
        if (($id = $this->parseIdentifierReference($yield)) ||
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
        if ($this->scanner->consume("export")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consume("*")) {
                
                if ($source = $this->parseFromClause()) {
                    $this->scanner->consume(";");
                    $node = $this->createNode(
                        "ExportAllDeclaration", $position
                    );
                    $node->setSource($source);
                    return $this->completeNode($node);
                }
                
            } elseif ($this->scanner->consume("default")) {
                
                if (($declaration = $this->parseHoistableDeclaration(true)) ||
                    ($declaration = $this->parseClassDeclaration(true))) {
                    
                    $node = $this->createNode(
                        "ExportDefaultDeclaration", $position
                    );
                    $node->setDeclaration($declaration);
                    return $this->completeNode($node);
                    
                } elseif ($this->scanner->notBefore(array("function", "class")) &&
                          ($declaration = $this->parseAssignmentExpression(true))) {
                    
                    $this->scanner->consume(";");
                    $node = $this->createNode(
                        "ExportDefaultDeclaration", $position
                    );
                    $node->setDeclaration($declaration);
                    return $this->completeNode($node);
                }
                
            } elseif (($specifiers = $this->parseExportClause()) !== null) {
                
                $node = $this->createNode("ExportNamedDeclaration", $position);
                $node->setSpecifiers($specifiers);
                if ($source = $this->parseFromClause()) {
                    $node->setSource($source);
                }
                $this->scanner->consume(";");
                return $this->completeNode($node);

            } elseif (($dec = $this->parseVariableStatement()) ||
                      $dec = $this->parseDeclaration()) {

                $node = $this->createNode("ExportNamedDeclaration", $position);
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
            
            $list = $this->charSeparatedListOf("parseExportSpecifier");
            $this->scanner->consume(",");
            
            if ($this->scanner->consume("}")) {
                return $list ? $list : array();
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseExportSpecifier()
    {
        if ($local = $this->parseIdentifierName()) {
            
            $node = $this->createNode("ExportSpecifier", $local);
            $node->setLocal($local);
            
            if ($this->scanner->consume("as")) {
                
                if ($exported = $this->parseIdentifierName()) {
                    $node->setExported($exported);
                    return $this->completeNode($node);
                }
                
                return $this->error();
            } else {
                return $this->completeNode($node);
            }
        }
        return null;
    }
    
    protected function parseImportDeclaration()
    {
        if ($this->scanner->consume("import")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($source = $this->parseStringLiteral()) {
                
                $this->scanner->consume(";");
                $node = $this->createNode("ImportDeclaration", $position);
                $node->setSource($source);
                return $this->completeNode($node);
                
            } elseif (($specifiers = $this->parseImportClause()) &&
                      $source = $this->parseFromClause()) {
                
                $this->scanner->consume(";");
                $node = $this->createNode("ImportDeclaration", $position);
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
            
            $node = $this->createNode("ImportNamespaceSpecifier", $spec);
            $node->setLocal($spec);
            return array($this->completeNode($node));
            
        } elseif ($specs = $this->parseNamedImports()) {
            return $specs;
        } elseif ($spec = $this->parseIdentifierReference()) {
            
            $node = $this->createNode("ImportSpecifier", $spec);
            $node->setLocal($spec);
            $ret = array($this->completeNode($node));
            
            if ($this->scanner->consume(",")) {
                
                if ($spec = $this->parseNameSpaceImport()) {
                    $node = $this->createNode(
                        "ImportNamespaceSpecifier", $spec
                    );
                    $node->setLocal($spec);
                    $ret[] = $this->completeNode($node);
                    return $ret;
                } elseif ($specs = $this->parseNamedImports()) {
                    $ret = array_merge($ret, $specs);
                    return $ret;
                }
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseNameSpaceImport()
    {
        if ($this->scanner->consume("*")) {
            
            if ($this->scanner->consume("as") &&
                $local = $this->parseIdentifierReference()) {
                return $local;  
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseNamedImports()
    {
        if ($this->scanner->consume("{")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $list = $this->charSeparatedListOf(
                "parseImportSpecifier", $position
            );
            $this->scanner->consume(",");
            
            if ($this->scanner->consume("}")) {
                return $list ? $list : array();
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseImportSpecifier()
    {
        if ($local = $this->parseIdentifierReference()) {
            
            $node = $this->createNode("ImportSpecifier", $local);
            $node->setLocal($local);
            return $node;
            
        } elseif ($local = $this->parseIdentifierName()) {
            
            $node = $this->createNode("ImportSpecifier", $local);
            $node->setLocal($local);
            if ($this->scanner->consume("as")) {
                
                if ($imported = $this->parseIdentifierName()) {
                    $node->setImported($imported);
                    return $this->completeNode($node);
                }
                
                return $this->error();
            } else {
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
        if ($this->scanner->consume("[")) {
            
            $node = $this->createNode(
                "ArrayPattern", $this->scanner->getConsumedTokenPosition()
            );
            $elements = $this->charSeparatedListOf(
                "parseBindingElisionElement",
                array($yield)
            );
            
            if (!$elements || $this->scanner->consume(",")) {
            
                $elision = $this->parseElision();
                $rest = $this->parseBindingRestElement($yield);
                
                if ($this->scanner->consume("]")) {
                    
                    if (!$elements) {
                        $elements = array();
                    }
                    
                    if ($elision && $elision > 1) {
                        $elements = array_merge(
                            $elements,
                            array_fill(0, $elision - 1, null)
                        );
                    }
                    
                    if ($rest) {
                        $elements[] = $rest;
                    }
                    
                    $node->setElements($elements);
                    return $this->completeNode($node);
                }
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseBindingRestElement($yield = false)
    {
        if ($this->scanner->consume("...")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($argument = $this->parseIdentifierReference($yield)) {
                $node = $this->createNode("RestElement", $position);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseBindingElisionElement($yield = false)
    {
        $elision = $this->parseElision();
        
        if ($element = $this->parseBindingElement($yield)) {
            $ret = $elision ? array_fill(0, $elision, null) : array();
            $ret[] = $element;
            return $ret;
        }
        
        return $this->error();
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
        if ($left = $this->parseIdentifierReference($yield)) {
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
        if ($prop = $this->parseLiteralPropertyName()) {
            return array($prop, false);
        } elseif ($prop = $this->parseComputedPropertyName($yield)) {
            return array($prop, true);
        }
        return null;
    }
    
    protected function parseLiteralPropertyName()
    {
        if ($name = $this->parseIdentifierName()) {
            return $name;
        } elseif ($name = $this->parseStringLiteral()) {
            return $name;
        } elseif ($name = $this->parseNumericLiteral()) {
            return $name;
        }
        return null;
    }
    
    protected function parseComputedPropertyName($yield = false)
    {
        if ($this->scanner->consume("[")) {
            
            if (($name = $this->parseAssignmentExpression(true, $yield)) &&
                $this->scanner->consume("]")) {
                return $name;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseMethodDefinition($yield = false)
    {
        if ($method = $this->parseGeneratorMethod($yield)) {
            return $method;
        }
        
        $position = null;
        $error = false;
        $kind = Node\MethodDefinition::KIND_METHOD;
        if ($this->scanner->consume("get")) {
            $position = $this->scanner->getConsumedTokenPosition();
            $kind = Node\MethodDefinition::KIND_GET;
            $error = true;
        } elseif ($this->scanner->consume("set")) {
            $position = $this->scanner->getConsumedTokenPosition();
            $kind = Node\MethodDefinition::KIND_SET;
            $error = true;
        }
        
        if ($prop = $this->parsePropertyName($yield)) {
            
            if (!$position) {
                $position = $prop;
            }
            $error = true;
            if ($this->scanner->consume("(")) {
                
                $fnPosition = $this->scanner->getConsumedTokenPosition();
                $params = array();
                if ($kind === Node\MethodDefinition::KIND_SET) {
                    $params = $this->parseBindingElement();
                } elseif ($kind === Node\MethodDefinition::KIND_METHOD) {
                    $params = $this->parseFormalParameterList();
                }

                if ($params !== null &&
                    $this->scanner->consume(")") &&
                    $this->scanner->consume("{") &&
                    (($body = $this->parseFunctionBody()) || true) &&
                    $this->scanner->consume("}")) {

                    if ($prop[0] instanceof Node\Identifier &&
                        $prop[0]->getName() === "constructor") {
                        $kind = Node\MethodDefinition::KIND_CONSTRUCTOR;
                    }

                    $nodeFn = $this->createNode(
                        "FunctionExpression", $fnPosition
                    );
                    $nodeFn->setParams($params);
                    $nodeFn->setBody($body);

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
        }
        return null;
    }
    
    protected function parseGeneratorMethod($yield = false)
    {
        if ($this->scanner->consume("*")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if (($prop = $this->parsePropertyName($yield)) &&
                $this->scanner->consume("(")) {
                
                $fnPosition = $this->scanner->getConsumedTokenPosition();
                $params = $this->parseFormalParameterList($yield);
                if ($params !== null &&
                    $this->scanner->consume(")") &&
                    $this->scanner->consume("{") &&
                    ($body = $this->parseFunctionBody(true)) &&
                    $this->scanner->consume("}")) {

                    $nodeFn = $this->createNode(
                        "FunctionExpression", $fnPosition
                    );
                    $nodeFn->setParams($params);
                    $nodeFn->setBody($body);
                    $nodeFn->setGenerator(true);

                    $node = $this->createNode("MethodDefinition", $position);
                    $node->setKey($prop[0]);
                    $node->setValue($this->completeNode($nodeFn));
                    $node->setKind($node::KIND_METHOD);
                    $node->setComputed($prop[1]);
                    return $this->completeNode($node);
                }
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseArrowFormalParameters($yield = false)
    {
        if ($this->scanner->consume("(")) {
            
            $params = $this->parseFormalParameterList($yield);
            
            if ($params !== null && $this->scanner->consume(")")) {
                return $params;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseArrowParameters($yield = false)
    {
        if ($param = $this->parseIdentifierReference($yield)) {
            return array($param);
        }
        return $this->parseArrowFormalParameters($yield);
    }
    
    protected function parseConciseBody($in = false)
    {
        if ($this->scanner->consume("{")) {
            
            if (($body = $this->parseFunctionBody()) &&
                $this->scanner->consume("}")) {
                return array($body, false);
            }
            
            return $this->error();
        } elseif ($this->notBefore(array("{")) &&
                  $body = $this->parseAssignmentExpression($in)) {
            return array($body, true);
        }
        return null;
    }
    
    protected function parseArrowFunction($in = false, $yield = false)
    {
        $position = $this->scanner->getPosition();
        if (($params = $this->parseArrowParameters($yield)) !== null &&
            $this->scanner->consumeWhitespacesAndComments(false) &&
            $this->scanner->consume("=>")) {
            
            if ($body = $this->parseConciseBody($in)) {
                $node = $this->createNode("ArrowFunctionExpression", $params);
                $node->setParams($params);
                $node->setBody($body[0]);
                $node->setExpression($body[1]);
                return $this->completeNode($node);
            }
            return $this->error();
        }
        $this->scanner->setPosition($position);
        return null;
    }
    
    protected function parseObjectLiteral($yield = false)
    {
        if ($this->scanner->consume("{")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $properties = $this->charSeparatedListOf(
                "parsePropertyDefinition",
                array($yield)
            );
            $this->scanner->consume(",");
            
            if ($this->scanner->consume("}")) {
                
                $node = $this->createNode("ObjectExpression", $position);
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
        $position = $this->scanner->getPosition();
        if ($property = $this->parseCoverInitializedName($yield)) {
            return $property;
        } elseif ($property = $this->parseIdentifierReference($yield)) {
            $node = $this->createNode("Property", $property);
            $node->setKey($property);
            $node->setValue($property);
            return $this->completeNode($node);
        } elseif (($property = $this->parsePropertyName($yield)) &&
                  $this->scanner->consume(":")) {

            if ($value = $this->parseAssignmentExpression(true, $yield)) {
                $node = $this->createNode("Property", $property);
                $node->setKey($property[0]);
                $node->setValue($value);
                $node->setComputed($property[1]);
                return $this->completeNode($node);
            }

            return $this->error();
            
        } else {
            
            $this->scanner->setPosition($position);
            if ($property = $this->parseMethodDefinition($yield)) {

                $node = $this->createNode("Property", $property);
                $node->setKey($property->getKey());
                $node->setValue($property->getValue());
                $node->setComputed($property->getComputed());
                $kind = $property->getKind();
                if ($kind !== Node\MethodDefinition::KIND_CONSTRUCTOR) {
                    $node->setKind($kind);
                }
                return $this->completeNode($node);
            }
        }
        return null;
    }
    
    protected function parseCoverInitializedName($yield = false)
    {
        $position = $this->scanner->getPosition();
        if ($key = $this->parseIdentifierReference($yield)) {
            
            if ($value = $this->parseInitializer(true, $yield)) {
                
                $node = $this->createNode("Property", $key);
                $node->setKey($key);
                $node->setValue($value);
                $node->setShorthand(true);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
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
        if ($this->scanner->consume("{")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $properties = $this->charSeparatedListOf(
                "parseBindingProperty",
                array($yield)
            );
            $this->scanner->consume(",");
            
            if ($this->scanner->consume("}")) {
                $node = $this->createNode("ObjectPattern", $position);
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
        $position = $this->scanner->getPosition();
        if (($key = $this->parsePropertyName($yield)) &&
            $this->scanner->consume(":")) {
            
            if ($value = $this->parseBindingElement($yield)) {
                $node = $this->createNode("AssignmentProperty", $key);
                $node->setKey($key);
                $node->setValue($value);
                return $this->completeNode($node);
            }
            
            return $this->error();
            
        } else {
            
            $this->scanner->setPosition($position);
            if ($property = $this->parseSingleNameBinding($yield)) {
                
                $node = $this->createNode("AssignmentProperty", $property);
                if ($property instanceof Node\AssignmentPattern) {
                    $node->setKey($property->getLeft());
                    $node->setValue($property->getRight());
                    $node->setShorthand(true);
                } else {
                    $node->setKey($property);
                    $node->setValue($property);
                }
                return $this->completeNode($node);
            }
        }
        $this->scanner->setPosition($position);
        return null;
    }
    
    protected function parseExpression($in = false, $yield = false)
    {
        $list = $this->charSeparatedListOf(
            "parseAssignmentExpression",
            array($in, $yield)
        );
        
        if (!$list) {
            return $list;
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
        if ($expr = $this->parseConditionalExpression($in, $yield)) {
            return $expr;
        } elseif ($yield && $expr = $this->parseYieldExpression($in)) {
            return $expr;
        } elseif ($expr = $this->parseArrowFunction($in, $yield)) {
            return $expr;
        } elseif ($left = $this->parseLeftHandSideExpression($yield)) {
            if ($this->scanner->consume("=")) {
                $operator = "=";
            } else {
                $operator = $this->scanner->conumeOneOf(array(
                    "*=", "/=", "%=", "+=", "-=", "<<=",
                    ">>=", ">>>=", "&=", "^=", "|="
                ));
            }
            
            if ($operator &&
                $right = $this->parseAssignmentExpression($in, $yield)) {
                
                $node = $this->createNode("AssignmentExpression", $left);
                $node->setLeft($left);
                $node->setOperator($operator);
                $node->setRight($right);
                return $this->completeNode($node);
                
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseConditionalExpression($in = false, $yield = false)
    {
        if ($test = $this->parseLogicalORExpression($in, $yield)) {
            
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
    
    protected function parseLogicalORExpression($in = false, $yield = false)
    {
        return $this->recursiveExpression(
            "parseLogicalANDExpression",
            array($in, $yield),
            "||",
            "LogicalExpression"
        );
    }
    
    protected function parseLogicalANDExpression($in = false, $yield = false)
    {
        return $this->recursiveExpression(
            "parseBitwiseORExpression",
            array($in, $yield),
            "||",
            "LogicalExpression"
        );
    }
    
    protected function parseBitwiseORExpression($in = false, $yield = false)
    {
        return $this->recursiveExpression(
            "parseBitwiseXORExpression",
            array($in, $yield),
            "|",
            "BinaryExpression"
        );
    }
    
    protected function parseBitwiseXORExpression($in = false, $yield = false)
    {
        return $this->recursiveExpression(
            "parseBitwiseANDExpression",
            array($in, $yield),
            "^",
            "BinaryExpression"
        );
    }
    
    protected function parseBitwiseANDExpression($in = false, $yield = false)
    {
        return $this->recursiveExpression(
            "parseEqualityExpression",
            array($in, $yield),
            "&",
            "BinaryExpression"
        );
    }
    
    protected function parseEqualityExpression($in = false, $yield = false)
    {
        return $this->recursiveExpression(
            "parseRelationalExpression",
            array($in, $yield),
            array("===", "!==", "==", "!="),
            "BinaryExpression"
        );
    }
    
    protected function parseRelationalExpression($in = false, $yield = false)
    {
        $chars = array("<=", ">=", "<", ">", "instanceof");
        if ($in) {
            $chars[] = "in";
        }
        return $this->recursiveExpression(
            "parseShiftExpression",
            array($yield),
            $chars,
            "BinaryExpression"
        );
    }
    
    protected function parseShiftExpression($yield = false)
    {
        return $this->recursiveExpression(
            "parseAdditiveExpression",
            array($yield),
            array(">>>", "<<", ">>"),
            "BinaryExpression"
        );
    }
    
    protected function parseAdditiveExpression($yield = false)
    {
        return $this->recursiveExpression(
            "parseMultiplicativeExpression",
            array($yield),
            array("+", "-"),
            "BinaryExpression"
        );
    }
    
    protected function parseMultiplicativeExpression($yield = false)
    {
        return $this->recursiveExpression(
            "parseUnaryExpression",
            array($yield),
            array("*", "/", "%"),
            "BinaryExpression"
        );
    }
    
    protected function parseUnaryExpression($yield = false)
    {
        if ($expr = $this->parsePostfixExpression($yield)) {
            return $expr;
        } else {
            
            $operator = $this->scanner->consumeOneOf(array(
                "delete", "void", "typeof", "++", "--", "+", "-", "~", "!"
            ));
            $position = $this->scanner->getConsumedTokenPosition();
            
            if ($operator) {
                if ($argument = $this->parseUnaryExpression($yield)) {
                    $node = $this->createNode("UnaryExpression", $position);
                    $node->setOperator($operator);
                    $node->setArgument($argument);
                    return $this->completeNode($node);
                }
            
                return $this->error();
            }
        }
        return null;
    }
    
    protected function parsePostfixExpression($yield = false)
    {
        if ($argument = $this->parseLeftHandSideExpression($yield)) {
            
            $subPosition = $this->scanner->getPosition();
            if ($this->scanner->consumeWhitespacesAndComments(false) !== null &&
                $operator = $this->scanner->consumeOneOf(array("--", "++"))) {
                
                $node = $this->createNode("UpdateExpression", $argument);
                $node->setOperator($operator);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            $this->scanner->setPosition($subPosition);
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
        if ($this->scanner->consume("...")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($argument = $this->parseAssignmentExpression(true, $yield)) {
                $node = $this->createNode("SpreadElement", $position);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseArrayLiteral($yield = false)
    {
        if ($this->scanner->consume("[")) {
            
            $node = $this->createNode(
                "ArrayExpression", $this->scanner->getConsumedTokenPosition()
            );
            $elements = $this->parseElementList($yield);
            
            if (!$elements || $this->scanner->consume(",")) {
            
                $elision = $this->parseElision();
                
                if ($this->scanner->consume("]")) {
                    
                    if (!$elements) {
                        $elements = array();
                    }
                    
                    if ($elision && $elision > 1) {
                        $elements = array_merge(
                            $elements,
                            array_fill(0, $elision - 1, null)
                        );
                    }
                    
                    $node->setElements($elements);
                    return $this->completeNode($node);
                }
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseElementList($yield = false)
    {
        $position = $this->scanner->getPosition();
        $begin = true;
        $list = array();
        while (true) {
            $ellision = $this->parseElision();
            if (!($el = $this->parseSpreadElement($yield))) {
                $el = $this->parseAssignmentExpression(true, $yield);
            }
            if (!$ellision && !$begin) {
                $this->scanner->setPosition($position);
                return null;
            } elseif (($begin && $ellision) || (!$begin && $ellision > 1)) {
                $list = array_merge(
                    $list,
                    array_fill(0, $begin ? $ellision : $ellision - 1, null)
                );
            }
            $begin = false;
            if (!$el) {
                break;
            }
            $list[] = $el;
        }
        return count($list) ? $list : array();
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
            $spread = false;
            if ($this->scanner->consume("...")) {
                $spreadPosition = $this->scanner->getConsumedTokenPosition();
                $spread = true;
            }
            $exp = $this->parseAssignmentExpression(true, $yield);
            if (!$exp) {
                $valid = $start && !$spread;
                break;
            }
            if ($spread) {
                $node = $this->createNode("SpreadElement", $spreadPosition);
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
        if ($this->scanner->consume("super")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if (($args = $this->parseArguments($yield)) !== null) {
                $super = $this->createNode("Super", $position);
                $node = $this->createNode("CallExpression", $position);
                $node->setArguments($args);
                $node->setCallee($this->completeNode($super));
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseNewTarget()
    {
        if ($this->scanner->consume("new")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($this->scanner->consume(".")) {
                
                if ($this->scanner->consume("target")) {
                    
                    $targetPosition = $this->scanner->getConsumedTokenPosition();
                    
                    $meta = $this->createNode("Identifier", $position);
                    $meta->setName("new");

                    $property = $this->createNode("Identifier", $targetPosition);
                    $property->setName("target");

                    $node = $this->createNode("MetaProperty", $position);
                    $node->setMeta($this->completeNode($meta));
                    $node->setProperty($this->completeNode($property));
                    return $this->completeNode($node);
                }
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseIdentifierReference($yield = false)
    {
        if ($identifier = $this->parseIdentifier()) {
            return $identifier;
        } elseif (!$yield && $this->scanner->consume("yield")) {
            $node = $this->createNode(
                "Identifier", $this->scanner->getConsumedTokenPosition()
            );
            $node->setName("yield");
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseMemberExpression($yield = false)
    {
        if ($this->scanner->consume("new")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if (($callee = $this->parseMemberExpression($yield)) &&
                $args = $this->parseArguments($yield)) {
                    
                $node = $this->createNode("NewExpression", $position);
                $node->setCallee($callee);
                $node->setArguments($args);
                return $this->completeNode($node);
            }
            
            return $this->error();
            
        } elseif (!($object = $this->parsePrimaryExpression($yield)) && 
            !($object = $this->parseSuperProperty($yield)) &&
            !($object = $this->parseNewTarget())) {
            return null;
        }
        
        $valid = true;
        $properties = array();
        while (true) {
            if ($this->scanner->consume(".")) {
                if ($property = $this->parseIdentifierName()) {
                    $properties[] = array($property, false);
                } else {
                    $valid = false;
                    break;
                }
            } elseif ($this->scanner->consume("[")) {
                if (($property = $this->parseExpression(true, $yield)) &&
                    $this->scanner->consume("]")) {
                    $properties[] = array($property, true);
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
        
        if (!count($properties)) {
            return $object;
        } elseif (!$valid) {
            return $this->error();
        }
        
        $lastIndex = count($properties) - 1;
        $node = $this->createNode("MemberExpression", $object);
        $node->setObject($object);
        foreach ($properties as $i => $property) {
            if (is_array($property)) {
                $node->setProperty($property[0]);
                if ($property[1]) {
                    $node->setComputed(true);
                }
            } else {
                $lastNode = $node;
                $node = $this->createNode("TaggedTemplateExpression", $object);
                $node->setTag($this->completeNode($lastNode));
                $node->setQuasi($property[0]);
            }
            if ($i !== $lastIndex) {
                $lastNode = $node;
                $node = $this->createNode("MemberExpression", $object);
                $node->setObject($this->completeNode($lastNode));
            }
        }
        
        return $this->completeNode($node);
    }
    
    protected function parseSuperProperty($yield = false)
    {
        if ($this->scanner->consume("super")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $super = $this->createNode("Super", $position);
            
            $node = $this->createNode("MemberExpression", $position);
            $node->setObject($this->completeNode($super));
            
            if ($this->scanner->consume(".")) {
                
                if ($property = $this->parseIdentifierName()) {
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
        if ($this->scanner->consume("new")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($callee = $this->parseNewExpression($yield)) {
                $node = $this->createNode("NewExpression", $position);
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
        if ($this->scanner->consume("this")) {
            $node = $this->createNode(
                "ThisExpression", $this->scanner->getConsumedTokenPosition()
            );
            return $this->completeNode($node);
        } elseif ($exp = $this->parseIdentifierReference($yield)) {
            return $exp;
        } elseif ($exp = $this->parseLiteral()) {
            return $exp;
        } elseif ($exp = $this->parseArrayLiteral($yield)) {
            return $exp;
        } elseif ($exp = $this->parseObjectLiteral($yield)) {
            return $exp;
        } elseif ($exp = $this->parseFunctionExpression()) {
            return $exp;
        } elseif ($exp = $this->parseClassExpression($yield)) {
            return $exp;
        } elseif ($exp = $this->parseGeneratorExpression()) {
            return $exp;
        } elseif ($exp = $this->parseRegularExpressionLiteral()) {
            return $exp;
        } elseif ($exp = $this->parseTemplateLiteral($yield)) {
            return $exp;
        } elseif ($this->scanner->consume("(")) {
            
            if (($exp = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")")) {
                return $exp;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseIdentifierName()
    {
        if ($identifier = $this->scanner->consumeIdentifier()) {
            $node = $this->createNode(
                "Identifier", $this->scanner->getConsumedTokenPosition()
            );
            $node->setName($identifier);
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseIdentifier()
    {
        if ($identifier = $this->parseIdentifierName()) {
            
            $reserverWords = $this->config->getReservedWords($this->moduleMode);
            if (!in_array($identifier->getName(), $reserverWords)) {
                return $identifier;
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseCallExpression($yield = false)
    {
        $position = $this->scanner->getPosition();
        $object = $this->parseSuperCall($yield);
        if (!$object) {
            
            $callee = $this->parseMemberExpression($yield);
            $args = $callee ? $this->parseArguments($yield) : null;
            
            if ($callee === null || $args === null) {
                $this->scanner->setPosition($position);
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
            if ($args = $this->parseArguments($yield)) {
                $properties[] = array($args, false);
            } elseif ($this->scanner->consume(".")) {
                if ($property = $this->parseIdentifierName()) {
                    $properties[] = array($property, false);
                } else {
                    $valid = false;
                    break;
                }
            } elseif ($this->scanner->consume("[")) {
                if (($property = $this->parseExpression(true, $yield)) &&
                    $this->scanner->consume("]")) {
                    $properties[] = array($property, true);
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
        
        if (!count($properties)) {
            return $object;
        } elseif (!$valid) {
            return $this->error();
        }
        
        $lastIndex = count($properties) - 1;
        $node = $this->createNode("MemberExpression", $object);
        $node->setObject($object);
        foreach ($properties as $i => $property) {
            if (is_array($property)) {
                if (is_array($property[0])) {
                    $lastNode = $node;
                    $node = $this->createNode("CallExpression", $object);
                    $node->setCallee($this->completeNode($lastNode));
                    $node->setArguments($property[0]);
                } else {
                    $node->setProperty($property[0]);
                    if ($property[1]) {
                        $node->setComputed(true);
                    }
                }
            } else {
                $lastNode = $node;
                $node = $this->createNode("TaggedTemplateExpression", $object);
                $node->setTag($this->completeNode($lastNode));
                $node->setQuasi($property[0]);
            }
            if ($i !== $lastIndex) {
                $lastNode = $node;
                $node = $this->createNode("MemberExpression", $position);
                $node->setObject($this->completeNode($lastNode));
            }
        }
        
        return $this->completeNode($node);
    }
    
    protected function parseLiteral()
    {
        if ($literal = $this->scanner->consumeOneOf(array(
                "null", "true", "false"
            ))) {
            $node = $this->createNode(
                "Literal", $this->scanner->getConsumedTokenPosition()
            );
            $node->setRaw($literal);
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
        if ($quote = $this->scanner->consumeOneOf(array("'", '"'))) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            if ($string = $this->scanner->consumeUntil(array($quote), false)) {
                $node = $this->createNode("Literal", $position);
                $node->setRaw($quote . $string);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseNumericLiteral()
    {
        if (($num = $this->scanner->consumeNumber()) !== null) {
            $node = $this->createNode(
                "Literal", $this->scanner->getConsumedTokenPosition()
            );
            $node->setRaw($num);
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseTemplateLiteral($yield = false)
    {
        if ($this->scanner->consume("`")) {
            
            $position = $this->scanner->getConsumedTokenPosition();
            $stops = array("`", "\${");
            $quasis = $expressions = array();
            while (true) {
                if (!($part = $this->scanner->consumeUntil($stops))) {
                    break;
                }
                if ($part[strlen($part) - 1] === "`") {
                    
                    $part = substr($part, 0, -1);
                    $quasi = $this->createNode(
                        "TemplateElement",
                        count($expressions) ?
                        $this->scanner->getConsumedTokenPosition() :
                        $position
                    );
                    $quasi->setRawValue($part);
                    $quasi->setTail(true);
                    $quasis[] = $this->completeNode($quasi);
                    
                    $node = $this->createNode("TemplateLiteral", $position);
                    $node->setQuasis($quasis);
                    $node->setExpressions($expressions);
                    return $this->completeNode($node);
                    
                } else {
                    
                    $part = preg_replace("/\{\$$/", "", $part);
                    
                    if (!($exp = $this->parseExpression(true, $yield))) {
                        break;
                    }
                    
                    $quasi = $this->createNode(
                        "TemplateElement", $$this->scanner->getPosition()
                    );
                    $quasi->setRawValue($part);
                    $quasis[] = $this->completeNode($quasi);
                    $expressions[] = $exp;
                }
            }
            
            return $this->error();
        }
        return null;
    }
    
    protected function parseRegularExpressionLiteral()
    {
        if ($regex = $this->scanner->consumeRegularExpression()) {
            $node = $this->createNode(
                "RegExpLiteral", $this->scanner->getConsumedTokenPosition()
            );
            $node->setRaw($regex);
            return $this->completeNode($node);
        }
        return null;
    }
}