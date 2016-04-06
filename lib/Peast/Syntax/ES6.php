<?php
namespace Peast\Syntax;

use Peast\Syntax\Node;

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
            
            $node = $this->createNode("ExpressionStatement");
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
                $notBeforeSB = $this->scanner->notBefore(array("let", "["));
                
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
    
    protected function parseFormalsList($yield = false)
    {
        return $this->charSeparatedListOf(
            "parseFormalParameter",
            array($yield)
        );
    }
    
    protected function parseFunctionRestParameter($yield = false)
    {
        return $this->parseBindingRestElement($yield);
    }
    
    protected function parseFormalParameter($yield = false)
    {
        return $this->parseBindingElement($yield);
    }
    
    protected function parseFunctionBody($yield = false)
    {
        return $this->parseFunctionStatementList($yield);
    }
    
    protected function parseFunctionStatementList($yield = false)
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
        if ($this->scanner->consume(";")) {
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
            
            if ($declarations && $this->scanner->consume(";")) {
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
        if ($this->scanner->consume("let")) {
            return "let";
        } elseif ($this->scanner->consume("const")) {
            return "const";
        }
        return null;
    }
    
    protected function parseBindingList($in = false, $yield = false)
    {
        return $this->charSeparatedListOf(
            "parseLexicalBinding",
            array($in, $yield)
        );
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
            
            if ($declarations && $this->scanner->consume(";")) {
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
        return $this->charSeparatedListOf(
            "parseVariableDeclaration",
            array($in, $yield)
        );
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
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("from") &&
            $spec = $this->parseModuleSpecifier()) {
            return $spec;
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseModuleSpecifier()
    {
        return $this->parseStringLiteral();
    }
    
    protected function parseExportDeclaration()
    {
        if ($this->scanner->consume("export")) {
            
            $position = $this->scanner->getPosition();
            
            if ($this->scanner->consume("*")) {
                
                $source = $this->parseFromClause();
                
                if ($source !== null && $this->scanner->consume(";")) {
                    
                    $node = $this->createNode("ExportAllDeclaration");
                    $node->setSource($source);
                    return $this->completeNode($node);
                    
                }
                
            } elseif ($this->scanner->consume("default")) {
                
                if (($declaration = $this->parseHoistableDeclaration(true)) ||
                    ($declaration = $this->parseClassDeclaration(true))) {
                    
                    $node = $this->createNode("ExportDefaultDeclaration");
                    $node->setDeclaration($declaration);
                    return $this->completeNode($node);
                    
                } elseif ($this->scanner->notBefore(array("function", "class")) &&
                          ($declaration = $this->parseAssignmentExpression(true)) &&
                          $this->scanner->consume(";")) {
                    
                    $node = $this->createNode("ExportDefaultDeclaration");
                    $node->setDeclaration($declaration);
                    return $this->completeNode($node);
                    
                } elseif (($specifiers = $this->parseExportClause()) !== null) {
                    
                    $source = $this->parseFromClause();
                    
                    if ($this->scanner->consume(";")) {
                        $node = $this->createNode("ExportNamedDeclaration");
                        $node->setSpecifiers($specifiers);
                        if ($source !== null) {
                            $node->setSource($source);
                        }
                        return $this->completeNode($node);
                    }
                    
                } elseif (($dec = $this->parseVariableStatement()) ||
                          $dec = $this->parseDeclaration()) {
                    
                    $node = $this->createNode("ExportNamedDeclaration");
                    $node->setDeclaration($dec);
                    return $this->completeNode($node);
                    
                }
                
            }
            
            $this->scanner->setPosition($position);
            
        }
        
        return null;
    }
    
    protected function parseExportClause()
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("{")) {
            
            $list = $this->parseExportsList();
            $this->scanner->consume(",");
            
            if ($this->scanner->consume("}")) {
                return $list ? $list : array();
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseExportsList()
    {
        return $this->charSeparatedListOf(
            "parseExportSpecifier",
            array($yield)
        );
    }
    
    protected function parseExportSpecifier()
    {
        if ($local = $this->parseIdentifierName()) {
            
            $position = $this->scanner->getPosition();
            $node = $this->createNode("ExportSpecifier");
            $node->setLocal($local);
            
            if ($this->scanner->consume("as")) {
                
                if ($exported = $this->parseIdentifierName()) {
                    
                    $node->setExported($exported);
                    return $this->completeNode($node);
                    
                }
                
                $this->scanner->setPosition($position);
                
            } else {
                return $this->completeNode($node);
            }
            
        }
        
        return null;
    }
    
    protected function parseImportDeclaration()
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("import")) {
            
            if ($source = $this->parseModuleSpecifier()) {
                
                $node = $this->createNode("ImportDeclaration");
                $node->setSource($source);
                
                if ($this->scanner->consume(";")) {
                    return $this->completeNode($node);
                }
                
            } elseif (($specifiers = $this->parseImportClause()) &&
                      $source = $this->parseFromClause()) {
                
                $node = $this->createNode("ImportDeclaration");
                $node->setSpecifiers($specifiers);
                $node->setSource($source);
                
                if ($this->scanner->consume(";")) {
                    return $this->completeNode($node);
                }
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseImportClause()
    {
        $position = $this->scanner->getPosition();
        
        if ($spec = $this->parseNameSpaceImport()) {
            
            $node = $this->createNode("ImportNamespaceSpecifier");
            $node->setLocal($spec);
            return array($this->completeNode($node));
            
        } elseif ($specs = $this->parseNamedImports()) {
            
            return $specs;
            
        } elseif ($spec = $this->parseImportedDefaultBinding()) {
            
            $node = $this->createNode("ImportSpecifier");
            $node->setLocal($spec);
            $ret = array($this->completeNode($node));
            
            if ($this->scanner->consume(",")) {
                
                if ($spec = $this->parseNameSpaceImport()) {
                    
                    $node = $this->createNode("ImportNamespaceSpecifier");
                    $node->setLocal($spec);
                    $ret[] = $this->completeNode($node);
                    return $ret;
                    
                } elseif ($specs = $this->parseNamedImports()) {
                    
                    $ret = array_merge($ret, $specs);
                    return $ret;
                    
                }
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseNameSpaceImport()
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consumeArray("*", "as") &&
            $local = $this->parseImportedBinding()) {
            return $local;        
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseImportedBinding()
    {
        return $this->parseBindingIdentifier();
    }
    
    protected function parseNamedImports()
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("{")) {
            
            $list = $this->parseExportsList();
            $this->scanner->consume(",");
            
            if ($this->scanner->consume("}")) {
                return $list ? $list : array();
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseImportsList()
    {
        return $this->charSeparatedListOf(
            "parseImportSpecifier",
            array($yield)
        );
    }
    
    protected function parseImportSpecifier()
    {
        if ($local = $this->parseImportedBinding()) {
            
            $node = $this->createNode("ImportSpecifier");
            $node->setLocal($local);
            return $node;
            
        } elseif ($local = $this->parseIdentifierName()) {
            
            $position = $this->scanner->getPosition();
            $node = $this->createNode("ImportSpecifier");
            $node->setLocal($local);
            
            if ($this->scanner->consume("as")) {
                
                if ($imported = $this->parseIdentifierName()) {
                    
                    $node->setImported($imported);
                    return $this->completeNode($node);
                    
                }
                
                $this->scanner->setPosition($position);
                
            } else {
                return $this->completeNode($node);
            }
            
        }
        
        return null;
    }
    
    protected function parseImportedDefaultBinding()
    {
        return $this->parseImportedBinding();
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
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("[")) {
            
            $node = $this->createNode("ArrayPattern");
            $elements = $this->parseBindingElementList($yield);
            
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
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseBindingRestElement($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("...") &&
            $argument = $this->parseBindingIdentifier($yield)) {
                
            $node = $this->createNode("RestElement");
            $node->setArgument($argument);
            return $this->completeNode($node);
            
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseBindingElementList($yield = false)
    {
        return $this->charSeparatedListOf(
            "parseBindingElisionElement",
            array($yield)
        );
    }
    
    protected function parseBindingElisionElement($yield = false)
    {
        $position = $this->scanner->getPosition();
        $elision = $this->parseElision();
        
        if ($element = $this->parseBindingElement($yield)) {
            $ret = $elision ? array_fill(0, $elision, null) : array();
            $ret[] = $element;
            return $ret;
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseBindingElement($yield = false)
    {
        if ($el = $this->parseSingleNameBinding($yield)) {
            
            return $el;
            
        } elseif ($left = $this->parseBindingPattern($yield)) {
            
            if ($right = $this->parseInitializer(true, $yield)) {
                
                $node = $this->createNode("AssignmentPattern");
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
        if ($left = $this->parseBindingIdentifier($yield)) {
            
            if ($right = $this->parseInitializer(true, $yield)) {
                
                $node = $this->createNode("AssignmentPattern");
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
            return array($prop, false);
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
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("[")) {
            
            if (($name = $this->parseAssignmentExpression(true, $yield)) &&
                $this->scanner->consume("]")) {
                return $name;
            }
            
            $this->scanner->setPosition($position);
            
        }
        return null;
    }
    
    protected function parseMethodDefinition($yield = false)
    {
        if ($method = $this->parseGeneratorMethod($yield)) {
            return $method;
        }
        
        $position = $this->scanner->getPosition();
        
        $kind = Node\MethodDefinition::KIND_METHOD;
        if ($this->scanner->consume("get")) {
            $kind = Node\MethodDefinition::KIND_GET;
        } elseif ($this->scanner->consume("set")) {
            $kind = Node\MethodDefinition::KIND_SET;
        }
        
        if (($prop = $this->parsePropertyName($yield)) &&
            $this->scanner->consume("(")) {
            
            $params = array();
            if ($kind === Node\MethodDefinition::KIND_SET) {
                $params = $this->parsePropertySetParameterList();
            } elseif ($kind === Node\MethodDefinition::KIND_METHOD) {
                $params = $this->parseStrictFormalParameters();
            }
            
            if ($this->scanner->consume(")") &&
                $this->scanner->consume("{") &&
                ($body = $this->parseFunctionBody()) &&
                $this->scanner->consume("{")) {
                
                if ($prop[0] instanceof Node\Identifier &&
                    $prop[0]->getName() === "constructor") {
                    $kind = Node\MethodDefinition::KIND_CONSTRUCTOR;
                }
                
                $nodeFn = $this->createNode("FunctionExpression");
                $nodeFn->setParams($params);
                $nodeFn->setBody($body);
                
                $node = $this->createNode("MethodDefinition");
                $node->setKey($prop[0]);
                $node->setValue($this->completeNode($nodeFn));
                $node->setKind($kind);
                $node->setComputed($prop[1]);
                return $this->completeNode($node);
                
            }
            
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseGeneratorMethod($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("*")) {
            
            if (($prop = $this->parsePropertyName($yield)) &&
                $this->scanner->consume("(") &&
                (($params = $this->parseStrictFormalParameters($yield)) || true) &&
                $this->scanner->consume(")") &&
                $this->scanner->consume("{") &&
                ($body = $this->parseGeneratorBody()) &&
                $this->scanner->consume("}")) {
                
                $nodeFn = $this->createNode("FunctionExpression");
                $nodeFn->setParams($params);
                $nodeFn->setBody($body);
                $nodeFn->setGenerator(true);
                
                $node = $this->createNode("MethodDefinition");
                $node->setKey($prop[0]);
                $node->setValue($this->completeNode($nodeFn));
                $node->setKind($node::KIND_METHOD);
                $node->setComputed($prop[1]);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
            
        }
        return null;
    }
    
    protected function parsePropertySetParameterList()
    {
        return $this->parseFormalParameter();
    }
    
    protected function parseArrowFormalParameters($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("(")) {
            
            $params = $this->parseStrictFormalParameters($yield);
            
            if ($this->scanner->consume(")")) {
                return $params;
            }
            
            $this->scanner->setPosition($position);
        }
        return null;
    }
    
    protected function parseArrowParameters($yield = false)
    {
        if ($param = $this>parseBindingIdentifier($yield)) {
            return array($param);
        }
        return $this->parseArrowFormalParameters($yield);
    }
    
    protected function parseConciseBody($in = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("{")) {
            
            if (($body = $this->FunctionBody()) &&
                $this->scanner->consume("}")) {
                return array($body, false);
            }
            
            $this->scanner->setPosition($position);
            
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
            $this->scanner->notBeforeLineTerminator() &&
            $this->scanner->consume("=>") &&
            $body = $this->parseConciseBody($in)) {
            
            $node = $this->createNode("ArrowFunctionExpression");
            $node->setParams($params);
            $node->setBody($body[0]);
            $node->setExpression($body[1]);
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseObjectLiteral($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("{")) {
            
            $properties = $this->parsePropertyDefinitionList($yield);
            $this->scanner->consume(",");
            
            if ($this->scanner->consume("}")) {
                
                $node = $this->createNode("ObjectExpression");
                if ($properties) {
                    $node->setProperties($properties);
                }
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parsePropertyDefinitionList($yield = false)
    {
        return $this->charSeparatedListOf(
            "parsePropertyDefinition",
            array($yield)
        );
    }
    
    protected function parsePropertyDefinition($yield = false)
    {
        if ($property = $this->parseCoverInitializedName($yield)) {
            
            return $property;
            
        } elseif ($property = $this->parseIdentifierReference($yield)) {
            
            $node = $this->createNode("Property");
            $node->setKey($property);
            $node->setValue($property);
            return $this->completeNode($node);
            
        } else {
            
            $position = $this->scanner->getPosition();
            
            if (($property = $this->parsePropertyName($yield)) &&
                $this->scanner->consume(":") &&
                $value = $this->parseAssignmentExpression(true, $yield)) {
                
                $node = $this->createNode("Property");
                $node->setKey($property[0]);
                $node->setValue($value);
                $node->setComputed($property[1]);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
            
            if ($property = $this->parseMethodDefinition($yield)) {
                
                $node = $this->createNode("Property");
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
                
                $node = $this->createNode("Property");
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
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("=")) {
            
            if ($value = $this->parseAssignmentExpression($in, $yield)) {
                return $value;
            }
            
            $this->scanner->setPosition($position);
        }
        return null;
    }
    
    protected function parseObjectBindingPattern($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("{")) {
            
            $properties = $this->parseBindingPropertyList($yield);
            $this->scanner->consume(",");
            
            if ($this->scanner->consume("}")) {
                
                $node = $this->createNode("ObjectPattern");
                if ($properties) {
                    $node->setProperties($properties);
                }
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseBindingPropertyList($yield = false)
    {
        return $this->charSeparatedListOf(
            "parseBindingProperty",
            array($yield)
        );
    }
    
    protected function parseBindingProperty($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if (($key = $this->parsePropertyName($yield)) &&
            $this->scanner->consume(":") &&
            $value = $this->parseBindingElement($yield)) {
                
            $node = $this->createNode("AssignmentProperty");
            $node->setKey($key);
            $node->setValue($value);
            return $this->completeNode($node);
            
        } else {
            
            $this->scanner->setPosition($position);
            
            if ($property = $this->parseSingleNameBinding($yield)) {
                
                $node = $this->createNode("AssignmentProperty");
                
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
        
        if ($list === null) {
            return $list;
        } elseif (count($list) === 1) {
            return $list[0];
        } else {
            $node = $this->createNode("SequenceExpression");
            $node->setExpressions($list);
            return $this->completeNode($node);
        }
    }
    
    protected function parseAssignmentExpression($in = false, $yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($expr = $this->parseAssignmentExpression($in, $yield)) {
            return $expr;
        } elseif ($yield && $expr = $this->parseYieldExpression($in)) {
            return $expr;
        } elseif ($expr = $this->parseArrowFunction($in, $yield)) {
            return $expr;
        } elseif ($left = $this->parseLeftHandSideExpression($yield)) {
            
            if ($this->scanner->consume("=")) {
                $operator = "=";
            } else {
                $operator = $this->parseAssignmentOperator();
            }
            
            if ($operator &&
                $right = $this->parseAssignmentExpression($in, $yield)) {
                
                $node = $this->createNode("AssignmentExpression");
                $node->setLeft($left);
                $node->setOperator($operator);
                $node->setRight($right);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parseAssignmentOperator()
    {
        return $this->scanner->conumeOneOf(array(
            "*=", "/=", "%=", "+=", "-=", "<<=", ">>=", ">>>=", "&=", "^=", "|="
        ));
    }
    
    protected function parseConditionalExpression($in = false, $yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($test = $this->parseLogicalORExpression($in, $yield)) {
            
            if ($this->scanner->consume("?")) {
                
                if (($consequent = $this->parseAssignmentExpression($in, $yield)) &&
                    $this->scanner->consume(":") &&
                    $alternate = $this->parseAssignmentExpression($in, $yield)) {
                
                    $node = $this->createNode("ConditionalExpression");
                    $node->setTest($test);
                    $node->setConsequent($consequent);
                    $node->setAlternate($alternate);
                    return $this->completeNode($node);
                    
                }
            } else {
                return $test;
            }
            
            $this->scanner->setPosition($position);
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
            "parseLogicalANDExpression",
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
        $position = $this->scanner->getPosition();
        
        if ($expr = $this->scanner->parsePostfixExpression($yield)) {
            return $expr;
        } else {
            
            $operator = $this->scanner->consumeOneOf(array(
                "delete", "void", "typeof", "++", "--", "+", "-", "~", "!"
            ));
            
            if ($operator && $argument = $this->parseUnaryExpression($yield)) {
                
                $node = $this->createNode("UnaryExpression");
                $node->setOperator($operator);
                $node->setArgument($argument);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($position);
        }
        
        return null;
    }
    
    protected function parsePostfixExpression($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($expr = $this->parseLeftHandSideExpression($yield)) {
            
            $subPosition = $this->scanner->getPosition();
            
            if ($this->scanner->notBeforeLineTerminator() &&
                $operator = $this->scanner->consumeOneOf("--", "++")) {
                    
                $node = $this->createNode("UpdateExpression");
                $node->setOperator($operator);
                $node->setArgument($argument);
                return $this->completeNode($node);
                
            }
            
            $this->scanner->setPosition($subPosition);
            
            return $expr;
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseLeftHandSideExpression($yield = false)
    {
        if ($expr = $this->parseNewExpression($yield)) {
            return $expr;
        } elseif ($expr = $this->parseCallExpression($yield)) {
            return $expr;
        }
        
        return null;
    }
    
    protected function parseSpreadElement($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("...") &&
            $argument = $this->parseAssignmentExpression(true, $yield)) {
                
            $node = $this->createNode("SpreadElement");
            $node->setArgument($argument);
            return $this->completeNode($node);
            
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseArrayLiteral($yield = false)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("[")) {
            
            $node = $this->createNode("ArrayExpression");
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
            
            $this->scanner->setPosition($position);
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
            } elseif (($begin && $ellision) || ($begin && $ellision > 1)) {
                $list = array_merge(
                    $list,
                    array_fill(0, $begin ? $elision : $ellision - 1, null)
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
}