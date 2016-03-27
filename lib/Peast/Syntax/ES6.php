<?php
namespace Peast\Syntax;

class ES6 extends Parser
{
    protected $moduleMode = false;
    
    public function __construct($module = false)
    {
        $this->moduleMode = $module;
    }
    
    public function parse()
    {
        if ($this->$this->moduleMode) {
            return $this->parseModule();
        } else {
            return $this->parseScript();
        }
    }
    
    protected function parseScript()
    {
        $body = $this->parseScriptBody();
        if ($body !== null) {
            $node = $this->createNode("Program");
            $node->setSourceType($node::SOURCE_TYPE_SCRIPT);
            if ($body) {
                $node->setBody($body);
            }
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseScriptBody()
    {
        return $this->parseStatementList();
    }
    
    protected function parseModule()
    {
        $body = $this->parseModuleBody();
        if ($body !== null) {
            $node = $this->createNode("Program");
            $node->setSourceType($node::SOURCE_TYPE_MODULE);
            if ($body) {
                $node->setBody($body);
            }
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseModuleBody()
    {
        return $this->parseModuleItemList();
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
        if ($statement = $this->parseBlockStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseVariableStatement($yield)) {
            return $statement;
        } elseif ($statement = $this->parseEmptyStatement()) {
            return $statement;
        } elseif ($statement = $this->parseExpressionStatement($yield)) {
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
        } elseif ($statement = $this->parseLabelledStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseThrowStatement($yield)) {
            return $statement;
        } elseif ($statement = $this->parseTryStatement($yield, $return)) {
            return $statement;
        } elseif ($statement = $this->parseDebuggerStatement()) {
            return $statement;
        }
        return null;
    }
    
    protected function parseDeclaration($yield)
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
    
    protected function parseBlockStatement($yield = false, $return = false)
    {
        $body = $this->parseBlock($yield, $return);
        if ($body !== null) {
            $node = $this->createNode("BlockStatement");
            if ($body) {
                $node->setBody($body);
            }
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseBlock($yield = false, $return = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("{")) {
            $statements = $this->parseStatementList($yield, $return);
            if ($this->scanner->consume("}")) {
                return $statements ? $statements : array();
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseModuleItemList()
    {
        $items = array();
        while ($item = $this->parseModuleItem($yield, $return)) {
            $items[] = $item;
        }
        return count($items) ? $items : null;
    }
    
    protected function parseEmptyStatement()
    {
        if ($this->scanner->consume(";")) {
            $node = $this->createNode("BlockStatement");
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseDebuggerStatement()
    {
        if ($this->scanner->consumeArray(array("debugger", ";"))) {
            $node = $this->createNode("DebuggerStatement");
            return $this->completeNode($node);
        }
        return null;
    }
    
    protected function parseIfStatement($yield = false, $return = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consumeArray("if", "(") &&
            ($test = $this->parseExpression(true, $yield)) &&
            $this->scanner->consume(")") &&
            $consequent = $this->parseStatement($yield, $return)) {
                
            $node = $this->createNode("IfStatement");
            $node->setTest($test);
            $node->setConsequent($consequent);
            
            if ($this->scanner->consume("else") &&
                $alternate = $this->parseStatement($yield, $return)) {
                $node->setAlternate($alternate);
            }
            
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseTryStatement($yield = false, $return = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("try") &&
            $block = $this->parseBlock($yield, $return)) {
                
            $node = $this->createNode("TryStatement");
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
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseCatch($yield, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consumeArray("catch", "(") &&
            ($param = $this->parseCatchParameter($yield)) &&
            $this->scanner->consume(")") &&
            $body = $this->parseBlock()) {
            
            $node = $this->createNode("CatchClause");
            $node->setParam($param);
            $node->setBody($body);
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseCatchParameter($yield)
    {
        if ($param = $this->parseBindingIdentifier($yield)) {
            return $param;
        } elseif ($param = $this->parseBindingPattern($yield)) {
            return $param;
        }
        return null;
    }
    
    protected function parseFinally($yield, $return)
    {
        if ($this->scanner->consume("finally") &&
            $block = $this->parseBlock($yield, $return)) {
            return $block;
        }
        return null;
    }
    
    protected function parseContinueStatement($yield)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("continue", false)) {
            $node = $this->createNode("ContinueStatement");
            
            if ($label = $this->parseLabelIdentifier($yield)) {
                $node->setLabel($label);
            }
            
            if ($this->scanner->consume(";")) {
                return $this->completeNode($node);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseBreakStatement($yield)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("break", false)) {
            $node = $this->createNode("BreakStatement");
            
            if ($label = $this->parseLabelIdentifier($yield)) {
                $node->setLabel($label);
            }
            
            if ($this->scanner->consume(";")) {
                return $this->completeNode($node);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseReturnStatement($yield)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("return", false)) {
            $node = $this->createNode("ReturnStatement");
            
            if ($argument = $this->parseExpression(true, $yield)) {
                $node->setArgument($argument);
            }
            
            if ($this->scanner->consume(";")) {
                return $this->completeNode($node);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseLabelledStatement($yield, $return)
    {
        $position = $this->scanner->getPosition();
        
        if (($label = $this->parseLabelIdentifier($yield)) &&
            $this->scanner->consume(":") &&
            $body = $this->parseLabelledItem($yield, $return)) {
            
            $node = $this->createNode("LabeledStatement");
            $node->setLabel($label);
            $node->setBody($body);
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseLabelledItem($yield, $return)
    {
        if ($statement = $this->parseStatement($yield, $return)) {
            return $statement;
        } elseif ($function = $this->parseFunctionDeclaration($yield)) {
            return $function;
        }
        return null;
    }
    
    protected function parseThrowStatement($yield)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("throw", false) &&
            ($argument = $this->parseExpression(true, $yield)) &&
            $this->scanner->consume(";")) {
            
            $node = $this->createNode("ThrowStatement");
            $node->setArgument($argument);
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseSwitchStatement($yield, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consumeArray("switch", "(") &&
            ($discriminant = $this->parseExpression(true, $yield)) &&
            $this->scanner->consume(")")) {
            
            $cases = $this->parseCaseBlock($yield, $return);
            
            if ($cases !== null) {
                $node = $this->createNode("SwitchStatement");
                $node->setDiscriminant($discriminant);
                $node->setCases($cases);
                return $this->completeNode($node);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseCaseBlock($yield, $return)
    {
        $position = $this->scanner->getPosition();
        
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
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseCaseClauses($yield, $return)
    {
        $cases = array();
        while ($case = $this->parseCaseClauses($yield, $return)) {
            $cases[] = $case;
        }
        return count($cases) ? $cases : null;
    }
    
    protected function parseCaseClause($yield, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("case") &&
            ($test = $this->parseExpression(true, $yield)) &&
            $this->scanner->consume(":")) {
            
            $node = $this->createNode("SwitchCase");
            $node->setTest($test);
            
            if ($cases = $this->parseStatementList($yield, $return)) {
                $node->setCases($cases);
            }
            
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseDefaultClause($yield, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consumeArray("default", ":")) {
            
            $node = $this->createNode("SwitchCase");
            
            if ($cases = $this->parseStatementList($yield, $return)) {
                $node->setCases($cases);
            }
            
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseExpressionStatement($yield)
    {
        $position = $this->scanner->getPosition();
        
        $lookahead = array("{", "function", "class", array("let", "["));
        
        if ($this->scanner->notBefore($lookahead) &&
            ($expression = $this->parseExpression(true, $yield)) &&
            $this->scanner->consume(";")) {
            
            $node = $this->createNode("ExpressionSta");
            $node->setExpression($expression);
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseIterationStatement($yield = false, $return = false)
    {
        $position = $this->scanner->getPosition();

        if ($this->scanner->consume("do")) {
            
            if (($body = $this->parseStatement($yield, $return)) &&
                $this->scanner->consumeArray("while", "(") &&
                ($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")")) {
                    
                $node = $this->createNode("DoWhileStatement");
                $node->setBody($body);
                $node->setTest($test);
                return $this->completeNode($node);
                
            }
        } elseif ($this->scanner->consumeArray("while", "(")) {
            
            if (($test = $this->parseExpression(true, $yield)) &&
                $this->scanner->consume(")") &&
                $body = $this->parseStatement($yield, $return)) {
                    
                $node = $this->createNode("WhileStatement");
                $node->setTest($test);
                $node->setBody($body);
                return $this->completeNode($node);
                    
            }
        } elseif ($this->scanner->consumeArray("for", "(")) {
            
            if ($this->scanner->consume("var")) {
                
                $subPosition = $this->scanner->getPosition();
                
                if (($decl = $this->parseVariableDeclarationList($yield)) &&
                    $this->scanner->consume(";")) {
                    
                    $test = $this->parseExpression(true, $yield);
                    
                    if ($this->scanner->consume(";")) {
                        
                        $update = $this->parseExpression(true, $yield);
                        
                        if ($this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $init = $this->createNode("VariableDeclaration");
                            $init->setKind($init::KIND_VAR);
                            $init->setDeclarations($decl);
                            $init = $this->completeNode($init);
                            
                            $node = $this->createNode("ForStatement");
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
                        
                        $left = $this->createNode("VariableDeclaration");
                        $left->setKind($left::KIND_VAR);
                        $left->setDeclarations(array($decl));
                        $left = $this->completeNode($left);
                        
                        if ($this->scanner->consume("in")) {
                            
                            if (($right = $this->parseExpression(true, $yield)) &&
                                $this->scanner->consume(")") &&
                                $body = $this->parseStatement($yield, $return)) {
                                
                                $node = $this->createNode("ForInStatement");
                                $node->setLeft($left);
                                $node->setRight($right);
                                $node->setBody($body);
                                return $this->completeNode($node);
                                
                            }
                            
                        } elseif ($this->scanner->consume("of")) {
                            
                            if (($right = $this->parseAssignmentExpression(true, $yield)) &&
                                $this->scanner->consume(")") &&
                                $body = $this->parseStatement($yield, $return)) {
                                
                                $node = $this->createNode("ForOfStatement");
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
                        
                        $node = $this->createNode("ForStatement");
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
                        
                        $node = $this->createNode("ForInStatement");
                        $node->setLeft($left);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                        
                    }
                    
                } elseif ($this->scanner->consume("of")) {
                    
                    if (($right = $this->parseAssignmentExpression(true, $yield)) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement($yield, $return)) {
                        
                        $node = $this->createNode("ForOfStatement");
                        $node->setLeft($left);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                        
                    }
                }
            } elseif ($this->scanner->notBefore(array("let"))) {
                
                $subPosition = $this->scanner->getPosition();
                $notBeforeSB = $this->scanner->notBefore(array("["));
                
                if ($notBeforeSB &&
                    (($init = $this->parseExpression(true, $yield)) || true) &&
                    $this->scanner->consume(";")) {
                
                    $test = $this->parseExpression(true, $yield);
                    
                    if ($this->scanner->consume(";")) {
                            
                        $update = $this->parseExpression(true, $yield);
                        
                        if ($this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $node = $this->createNode("ForStatement");
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
                            
                            $node = $this->createNode("ForInStatement");
                            $node->setLeft($left);
                            $node->setRight($right);
                            $node->setBody($body);
                            return $this->completeNode($node);
                            
                        }
                        
                    } elseif ($left && $this->scanner->consume("of")) {
                        
                        if (($right = $this->parseAssignmentExpression(true, $yield)) &&
                            $this->scanner->consume(")") &&
                            $body = $this->parseStatement($yield, $return)) {
                            
                            $node = $this->createNode("ForOfStatement");
                            $node->setLeft($left);
                            $node->setRight($right);
                            $node->setBody($body);
                            return $this->completeNode($node);
                            
                        }
                    
                    }
                }
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseFunctionDeclaration($yield = false, $default = false)
    {
        if ($this->scanner->consume("function")) {
            
            $position = $this->scanner->getPosition();
            $id = $this->BindingIdentifier($yield);
            
            if (($default || $id) &&
                $this->scanner->consume("(") &&
                ($params = $this->parseFormalParameters() || true) &&
                $this->scanner->consumeArray(array(")", "{")) &&
                ($body = $this->parseFunctionBody() || true) &&
                $this->scanner->consume("}")) {
                
                $node = $this->createNode("FunctionDeclaration");
                if ($id) {
                    $node->setId($id);
                }
                $node->setParams($params);
                $node->setBody($body);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseGeneratorDeclaration($yield = false, $default = false)
    {
        if ($this->scanner->consumeArray(array("function", "*"))) {
            
            $position = $this->scanner->getPosition();
            $id = $this->BindingIdentifier($yield);
            
            if (($default || $id) &&
                $this->scanner->consume("(") &&
                (($params = $this->parseFormalParameters(true)) || true) &&
                $this->scanner->consumeArray(array(")", "{")) &&
                (($body = $this->parseGeneratorBody()) || true) &&
                $this->scanner->consume("}")) {
                
                $node = $this->createNode("FunctionDeclaration");
                if ($id) {
                    $node->setId($id);
                }
                $node->setParams($params);
                $node->setBody($body);
                $node->setGenerator(true);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseFunctionExpression()
    {
        if ($this->scanner->consume("function")) {
            
            $position = $this->scanner->getPosition();
            $id = $this->BindingIdentifier();
            
            if ($this->scanner->consume("(") &&
                ($params = $this->parseFormalParameters() || true) &&
                $this->scanner->consumeArray(array(")", "{")) &&
                ($body = $this->parseFunctionBody() || true) &&
                $this->scanner->consume("}")) {
                
                $node = $this->createNode("FunctionExpression");
                $node->setId($id);
                $node->setParams($params);
                $node->setBody($body);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseGeneratorExpression()
    {
        if ($this->scanner->consumeArray(array("function", "*"))) {
            
            $position = $this->scanner->getPosition();
            $id = $this->BindingIdentifier(true);
            
            if ($this->scanner->consume("(") &&
                (($params = $this->parseFormalParameters(true)) || true) &&
                $this->scanner->consumeArray(array(")", "{")) &&
                (($body = $this->parseGeneratorBody()) || true) &&
                $this->scanner->consume("}")) {
                
                $node = $this->createNode("FunctionExpression");
                $node->setId($id);
                $node->setParams($params);
                $node->setBody($body);
                $node->setGenerator(true);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseGeneratorBody()
    {
        return $this->parseFunctionBody(true);
    }
    
    protected function parseYieldExpression($in = false)
    {
        if ($this->scanner->consume("yield", false)) {
            
            $position = $this->scanner->getPosition();
            $delegate = $this->scanner->consume("*") ? true : false;
            
            if ($argument = $this->parseAssignmentExpression($in, true)) {
                
                $node = $this->createNode("YieldExpression");
                $node->setArgument($argument);
                $node->setDelegate($delegate);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
            
        } elseif ($this->scanner->consume("yield")) {
            
            $node = $this->createNode("YieldExpression");
            return $this->completeNode($node);
        }
        
        return null;
    }
    
    protected function parseFormalParameters($yield)
    {
        $list = $this->parseFormalParametersList($yield);
        return $list ? $list : array();
    }
    
    protected function parseStrictFormalParameters($yield)
    {
        return $this->parseFormalParameters($yield);
    }
    
    protected function parseFormalParameterList($yield)
    {
        $params = $this->parseFormalsList($yield);
        if ($params) {
            
            $position = $this->scanner->getPosition();
            
            if ($this->scanner->consume(",") &&
                $rest = $this->parseFunctionRestParameter($yield)) {
                $params[] = $rest;
            } else {
                $this->scanner->setPosition($position);
            }
            
        } elseif ($rest = $this->parseFunctionRestParameter($yield)) {
            $params = array($rest);
        }
        
        return $params;
    }
    
    protected function parseFormalsList($yield)
    {
        $list = array();
        $position = null;
        while ($param = $this->parseFormalParameter($yield)) {
            $list[] = $param;
            if (!$this->scanner->consume(",")) {
                $position = null;
                break;
            } else {
                $position = $this->scanner->getPosition();
            }
        }
        if ($position) {
            $this->scanner->setPosition($position);
        }
        return count($list) ? $list : null;
    }
    
    protected function parseFunctionRestParameter($yield)
    {
        return $this->parseBindingRestElement($yield);
    }
    
    protected function parseFormalParameter($yield)
    {
        return $this->parseBindingElement($yield);
    }
    
    protected function parseFunctionBody($yield)
    {
        return $this->parseFunctionStatementList($yield);
    }
    
    protected function parseFunctionStatementList($yield)
    {
        $items = array();
        while ($item = $this->parseStatementList($yield, true)) {
            $items[] = $item;
        }
        return $items;
    }
    
    protected function parseClassDeclaration($yield = false, $default = false)
    {
        if ($this->scanner->consume("class")) {
            
            $position = $this->scanner->getPosition();
            $id = $this->BindingIdentifier($yield);
            
            if (($default || $id) &&
                $tail = $this->parseClassTail($yield)) {
                
                $node = $this->createNode("ClassDeclaration");
                if ($id) {
                    $node->setId($id);
                }
                if ($tail[0]) {
                    $node->setSuperClass($tail[0]);
                }
                $node->setBody($tail[1]);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseClassExpression($yield = false, $default = false)
    {
        if ($this->scanner->consume("class")) {
            
            $position = $this->scanner->getPosition();
            $id = $this->BindingIdentifier($yield);
            
            if ($tail = $this->parseClassTail($yield)) {
                
                $node = $this->createNode("ClassExpression");
                if ($id) {
                    $node->setId($id);
                }
                if ($tail[0]) {
                    $node->setSuperClass($tail[0]);
                }
                $node->setBody($tail[1]);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseClassTail($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        $heritage = $this->parseClassHeritage($yield);
        
        if ($this->scanner->consume("{")) {
            
            $body = $this->parseClassBody($yield);
            
            if ($this->scanner->consume("}")) {
                return array($heritage, $body);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseClassHeritage($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("extends") &&
            $superClass = $this->parseLeftHandSideExpression($yield)) {
            return $superClass;
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseClassBody($yield = false)
    {
        $body = $this->getClassElementList($yield);
        $node = $this->createNode("ClassBody");
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
        if ($this->consume(";")) {
            return true;
        } elseif ($def = $this->parseMethodDefinition($yield)) {
            return $def;
        }
        
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("static") &&
            $def = $this->parseMethodDefinition($yield)) {
            $def->setStatic(true);
            return $def;        
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseLexicalDeclaration($in = false, $yield = false)
    {
        if ($letOrConst = $this->parseLetOrConst()) {
            
            $position = $this->scanner->getPosition();
            $declarations = $this->parseBindingList($in, $yield);
            
            if ($declarations && $this->scanner->match(";")) {
                $node = $this->createNode("VariableDeclaration");
                $node->setKind($letOrConst);
                $node->setDeclarations($declarations);
                return $this->completeNode($node);
            }
            
            $this->scanner->setPosition($position);
            
        }
        
        return null;
    }
    
    protected function parseLetOrConst()
    {
        if ($this->match("let")) {
            return "let";
        } elseif ($this->match("const")) {
            return "const";
        }
        return null;
    }
    
    protected function parseBindingList($in = false, $yield = false)
    {
        $list = array();
        $position = null;
        while ($declaration = $this->parseLexicalBinding($in, $yield)) {
            $list[] = $declaration;
            if (!$this->scanner->consume(",")) {
                $position = null;
                break;
            } else {
                $position = $this->scanner->getPosition();
            }
        }
        if ($position) {
            $this->scanner->setPosition($position);
        }
        return count($list) ? $list : null;
    }
    
    protected function parseLexicalBinding($in = false, $yield = false)
    {
        if ($id = $this->parseBindingIdentifier($yield)) {
            
            $init = $this->parseInitializer($in, $yield);
            
            $node = $this->createNode("VariableDeclarator");
            $node->setId($id);
            if ($init) {
                $node->setInit($init);
            }
            return $this->completeNode($node);
            
        } else {
            
            $position = $this->scanner->getPosition();
            
            if (($id = $this->parseBindingPattern($yield)) &&
                $init = $this->parseInitializer($in, $yield)) {
                
                $node = $this->createNode("VariableDeclarator");
                $node->setId($id);
                $node->setInit($init);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseVariableStatement($yield = false)
    {
        if ($this->scanner->consume("var")) {
            
            $position = $this->scanner->getPosition();
            $declarations = $this->parseVariableDeclarationList(true, $yield);
            
            if ($declarations && $this->scanner->match(";")) {
                $node = $this->createNode("VariableDeclaration");
                $node->setKind($node::KIND_VAR);
                $node->setDeclarations($declarations);
                return $this->completeNode($node);
            }
            
            $this->scanner->setPosition($position);
            
        }
        
        return null;
    }
    
    protected function parseVariableDeclarationList($in = false, $yield = false)
    {
        $list = array();
        $position = null;
        while ($declaration = $this->parseVariableDeclaration($in, $yield)) {
            $list[] = $declaration;
            if (!$this->scanner->consume(",")) {
                $position = null;
                break;
            } else {
                $position = $this->scanner->getPosition();
            }
        }
        if ($position) {
            $this->scanner->setPosition($position);
        }
        return count($list) ? $list : null;
    }
    
    protected function parseVariableDeclaration($in = false, $yield = false)
    {
        if ($id = $this->parseBindingIdentifier($yield)) {
            
            $init = $this->parseInitializer($in, $yield);
            
            $node = $this->createNode("VariableDeclarator");
            $node->setId($id);
            if ($init) {
                $node->setInit($init);
            }
            return $this->completeNode($node);
            
        } else {
            
            $position = $this->scanner->getPosition();
            
            if (($id = $this->parseBindingPattern($yield)) &&
                $init = $this->parseInitializer($in, $yield)) {
                
                $node = $this->createNode("VariableDeclarator");
                $node->setId($id);
                $node->setInit($init);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseForDeclaration($yield = false)
    {
         $position = $this->scanner->getPosition();
        
        if ($letOrConst = $this->parseLetOrConst() &&
            $declaration = $this->parseForBinding($yield)) {
            
            $node = $this->createNode("VariableDeclaration");
            $node->setKind($letOrConst);
            $node->setDeclarations(array($declaration));
            return $this->completeNode($node);
            
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseForBinding($yield = false)
    {
        if (($init = $this->parseBindingIdentifier($yield)) ||
            ($init = $this->parseBindingPattern($yield))) {
            
            $node = $this->createNode("VariableDeclarator");
            $node->setId($id);
            return $this->completeNode($node);
            
        }
        
        return null;
    }
}