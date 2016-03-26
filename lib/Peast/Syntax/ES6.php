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
    
    protected function parseStatementList($yeld = false, $return = false)
    {
        $items = array();
        while ($item = $this->parseStatementListItem($yeld, $return)) {
            $items[] = $item;
        }
        return count($items) ? $items : null;
    }
    
    protected function parseStatementListItem($yeld = false, $return = false)
    {
        if ($statement = $this->parseStatement($yeld, $return)) {
            return $statement;
        } elseif ($declaration = $this->parseDeclaration($yeld)) {
            return $declaration;
        }
        return null;
    }
    
    protected function parseStatement($yeld = false, $return = false)
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
            $statements = $this->parseStatementList($yeld, $return);
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
        while ($item = $this->parseModuleItem($yeld, $return)) {
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
            $test = $this->parseExpression(true, $yeld) &&
            $this->scanner->consume(")") &&
            $consequent = $this->parseStatement($yeld, $return)) {
                
            $node = $this->createNode("IfStatement");
            $node->setTest($test);
            $node->setConsequent($consequent);
            
            if ($this->scanner->consume("else") &&
                $alternate = $this->parseStatement($yeld, $return)) {
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
            $block = $this->parseBlock($yeld, $return)) {
                
            $node = $this->createNode("TryStatement");
            $node->setBlock($block);
            
            if ($handler = $this->parseCatch($yeld, $return)) {
                $node->setHandler($handler);
            }
            
            if ($finalizer = $this->parseFinally($yeld, $return)) {
                $node->setFinalizer($finalizer);
            }
            
            if ($handler || $finalizer) {
                return $this->completeNode($node);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseCatch($yeld, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consumeArray("catch", "(") &&
            $param = $this->parseCatchParameter($yeld) &&
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
    
    protected function parseCatchParameter($yeld)
    {
        if ($param = $this->parseBindingIdentifier($yeld)) {
            return $param;
        } elseif ($param = $this->parseBindingPattern($yeld)) {
            return $param;
        }
        return null;
    }
    
    protected function parseFinally($yeld, $return)
    {
        if ($this->scanner->consume("finally") &&
            $block = $this->parseBlock($yeld, $return)) {
            return $block;
        }
        return null;
    }
    
    protected function parseContinueStatement($yeld)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("continue", false)) {
            $node = $this->createNode("ContinueStatement");
            
            if ($label = $this->parseLabelIdentifier($yeld)) {
                $node->setLabel($label);
            }
            
            if ($this->scanner->consume(";")) {
                return $this->completeNode($node);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseBreakStatement($yeld)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("break", false)) {
            $node = $this->createNode("BreakStatement");
            
            if ($label = $this->parseLabelIdentifier($yeld)) {
                $node->setLabel($label);
            }
            
            if ($this->scanner->consume(";")) {
                return $this->completeNode($node);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseReturnStatement($yeld)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("return", false)) {
            $node = $this->createNode("ReturnStatement");
            
            if ($argument = $this->parseExpression(true, $yeld)) {
                $node->setArgument($argument);
            }
            
            if ($this->scanner->consume(";")) {
                return $this->completeNode($node);
            }
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseLabelledStatement($yeld, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($label = $this->parseLabelIdentifier($yeld) &&
            $this->scanner->consume(":") &&
            $body = $this->parseLabelledItem($yeld, $return)) {
            
            $node = $this->createNode("LabeledStatement");
            $node->setLabel($label);
            $node->setBody($body);
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseLabelledItem($yeld, $return)
    {
        if ($statement = $this->parseStatement($yeld, $return)) {
            return $statement;
        } elseif ($function = $this->parseFunctionDeclaration($yeld)) {
            return $function;
        }
        return null;
    }
    
    protected function parseThrowStatement($yeld)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("throw", false) &&
            $argument = $this->parseExpression(true, $yeld) &&
            $this->scanner->consume(";")) {
            
            $node = $this->createNode("ThrowStatement");
            $node->setArgument($argument);
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseSwitchStatement($yeld, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consumeArray("switch", "(") &&
            $discriminant = $this->parseExpression(true, $yeld) &&
            $this->scanner->consume(")")) {
            
            $cases = $this->parseCaseBlock($yeld, $return);
            
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
    
    protected function parseCaseBlock($yeld, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("{")) {
            
            $parsedCasesAll = array(
                $this->parseCaseClauses($yeld, $return),
                $this->parseDefaultClause($yeld, $return),
                $this->parseCaseClauses($yeld, $return)
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
    
    protected function parseCaseClauses($yeld, $return)
    {
        $cases = array();
        while ($case = $this->parseCaseClauses($yeld, $return)) {
            $cases[] = $case;
        }
        return count($cases) ? $cases : null;
    }
    
    protected function parseCaseClause($yeld, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consume("case") &&
            $test = $this->parseExpression(true, $yeld) &&
            $this->scanner->consume(":")) {
            
            $node = $this->createNode("SwitchCase");
            $node->setTest($test);
            
            if ($cases = $this->parseStatementList($yeld, $return)) {
                $node->setCases($cases);
            }
            
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseDefaultClause($yeld, $return)
    {
        $position = $this->scanner->getPosition();
        
        if ($this->scanner->consumeArray("default", ":")) {
            
            $node = $this->createNode("SwitchCase");
            
            if ($cases = $this->parseStatementList($yeld, $return)) {
                $node->setCases($cases);
            }
            
            return $this->completeNode($node);
        }
        
        $this->scanner->setPosition($position);
        
        return null;
    }
    
    protected function parseExpressionStatement($yeld)
    {
        $position = $this->scanner->getPosition();
        
        $lookahead = array("{", "function", "class", array("let", "["));
        
        if ($this->scanner->notBefore($lookahead) &&
            $expression = $this->parseExpression(true, $yeld) &&
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
            
            if ($body = $this->parseStatement($yeld, $return) &&
                $this->scanner->consumeArray("while", "(") &&
                $test = $this->parseExpression(true, $yeld) &&
                $this->scanner->consume(")")) {
                    
                $node = $this->createNode("DoWhileStatement");
                $node->setBody($body);
                $node->setTest($test);
                return $this->completeNode($node);
                
            }
        } elseif ($this->scanner->consumeArray("while", "(")) {
            
            if ($test = $this->parseExpression(true, $yeld) &&
                $this->scanner->consume(")") &&
                $body = $this->parseStatement($yeld, $return)) {
                    
                $node = $this->createNode("WhileStatement");
                $node->setTest($test);
                $node->setBody($body);
                return $this->completeNode($node);
                    
            }
        } elseif ($this->scanner->consumeArray("for", "(")) {
            
            if ($this->scanner->consume("var")) {
                
                $subPosition = $this->scanner->getPosition();
                
                if ($init = $this->parseVariableDeclarationList($yeld) &&
                    $this->scanner->consume(";")) {
                    
                    $test = $this->parseExpression(true, $yeld);
                    
                    if ($this->scanner->consume(";")) {
                        
                        $update = $this->parseExpression(true, $yeld);
                        
                        if ($this->scanner->consume(")") &&
                            $body = $this->parseStatement($yeld, $return)) {
                            
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
                    
                    if ($left = $this->parseForBinding($yeld)) {
                        
                        if ($this->scanner->consume("in")) {
                            
                            if ($right = $this->parseExpression(true, $yeld) &&
                                $this->scanner->consume(")") &&
                                $body = $this->parseStatement($yeld, $return)) {
                                
                                $node = $this->createNode("ForInStatement");
                                $node->setLeft($left);
                                $node->setRight($right);
                                $node->setBody($body);
                                return $this->completeNode($node);
                                
                            }
                            
                        } elseif ($this->scanner->consume("of")) {
                            
                            if ($right = $this->parseAssignmentExpression(true, $yeld) &&
                                $this->scanner->consume(")") &&
                                $body = $this->parseStatement($yeld, $return)) {
                                
                                $node = $this->createNode("ForOfStatement");
                                $node->setLeft($left);
                                $node->setRight($right);
                                $node->setBody($body);
                                return $this->completeNode($node);
                                
                            }
                        }
                    }
                }
            } elseif ($init = $this->parseLexicalDeclaration($yeld)) {
                
                $test = $this->parseExpression(true, $yeld);
                
                if ($this->scanner->consume(";")) {
                        
                    $update = $this->parseExpression(true, $yeld);
                    
                    if ($this->scanner->consume(")") &&
                        $body = $this->parseStatement($yeld, $return)) {
                        
                        $node = $this->createNode("ForStatement");
                        $node->setInit($init);
                        $node->setTest($test);
                        $node->setUpdate($update);
                        $node->setBody($body);
                        return $this->completeNode($node);
                        
                    }
                }
            } elseif ($left = $this->parseForDeclaration($yeld)) {
                
                if ($this->scanner->consume("in")) {
                            
                    if ($right = $this->parseExpression(true, $yeld) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement($yeld, $return)) {
                        
                        $node = $this->createNode("ForInStatement");
                        $node->setLeft($left);
                        $node->setRight($right);
                        $node->setBody($body);
                        return $this->completeNode($node);
                        
                    }
                    
                } elseif ($this->scanner->consume("of")) {
                    
                    if ($right = $this->parseAssignmentExpression(true, $yeld) &&
                        $this->scanner->consume(")") &&
                        $body = $this->parseStatement($yeld, $return)) {
                        
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
                    (($init = $this->parseExpression(true, $yeld)) || true) &&
                    $this->scanner->consume(";")) {
                
                    $test = $this->parseExpression(true, $yeld);
                    
                    if ($this->scanner->consume(";")) {
                            
                        $update = $this->parseExpression(true, $yeld);
                        
                        if ($this->scanner->consume(")") &&
                            $body = $this->parseStatement($yeld, $return)) {
                            
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
                    $left = $this->parseLeftHandSideExpression($yeld);
                    
                    if ($notBeforeSB && $left &&
                        $this->scanner->consume("in")) {
                        
                        if ($right = $this->parseExpression(true, $yeld) &&
                            $this->scanner->consume(")") &&
                            $body = $this->parseStatement($yeld, $return)) {
                            
                            $node = $this->createNode("ForInStatement");
                            $node->setLeft($left);
                            $node->setRight($right);
                            $node->setBody($body);
                            return $this->completeNode($node);
                            
                        }
                        
                    } elseif ($left && $this->scanner->consume("of")) {
                        
                        if ($right = $this->parseAssignmentExpression(true, $yeld) &&
                            $this->scanner->consume(")") &&
                            $body = $this->parseStatement($yeld, $return)) {
                            
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
    
    protected function parseFunctionDeclaration($yeld = false, $default = false)
    {
        if ($this->scanner->consume("function")) {
            
            $position = $this->scanner->getPosition();
            $id = $this->BindingIdentifier($yeld);
            
            if (($default || $id) &&
                $this->scanner->consume("(") &&
                $params = $this->parseFormalParameters() &&
                $this->scanner->consumeArray(array(")", "{")) &&
                $body = $this->parseFunctionBody() &&
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
    
    protected function parseGeneratorDeclaration($yeld = false, $default = false)
    {
        if ($this->scanner->consumeArray(array("function", "*"))) {
            
            $position = $this->scanner->getPosition();
            $id = $this->BindingIdentifier($yeld);
            
            if (($default || $id) &&
                $this->scanner->consume("(") &&
                $params = $this->parseFormalParameters(true) &&
                $this->scanner->consumeArray(array(")", "{")) &&
                $body = $this->parseGeneratorBody() &&
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
                $params = $this->parseFormalParameters() &&
                $this->scanner->consumeArray(array(")", "{")) &&
                $body = $this->parseFunctionBody() &&
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
                $params = $this->parseFormalParameters(true) &&
                $this->scanner->consumeArray(array(")", "{")) &&
                $body = $this->parseGeneratorBody() &&
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
            
        } elseif ($this->scanner->consume("yield") {
            
            $node = $this->createNode("YieldExpression");
            return $this->completeNode($node);
        }
        
        return null;
    }
}