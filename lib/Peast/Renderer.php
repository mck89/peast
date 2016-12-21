<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast;

/**
 * Nodes renderer class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Renderer
{
    /**
     * Formatter to use for the rendering
     * 
     * @var Formatter\Base
     */
    protected $formatter;
    
    /**
     * Rendering options taken from the formatter
     * 
     * @var object
     */
    protected $renderOpts;
    
    /**
     * Node types that does not require semicolon insertion
     * 
     * @var array
     */
    protected $noSemicolon = array(
        "ClassDeclaration",
        "ForInStatement",
        "ForOfStatement",
        "ForStatement",
        "FunctionDeclaration",
        "IfStatement",
        "SwitchStatement",
        "TryStatement",
        "WhileStatement",
        "WithStatement",
    );
    
    /**
     * Sets the formatter to use for the rendering
     * 
     * @param Formatter\Base    $formatter  Formatter
     * 
     * @return $this
     */
    public function setFormatter(Formatter\Base $formatter)
    {
        $this->formatter = $formatter;
        
        $this->renderOpts = (object) array(
            "nl" => $this->formatter->getNewLine(),
            "ind" => $this->formatter->getIndentation(),
            "nlbc" => $this->formatter->getNewLineBeforeCurlyBracket(),
            "sao" =>  $this->formatter->getSpacesAroundOperator() ? " " : "",
            "sirb" => $this->formatter->getSpacesInsideRoundBrackets() ? " " : "",
            "awb" => $this->formatter->getAlwaysWrapBlocks()
        );
        
        return $this;
    }
    
    /**
     * Returns the formatter to use for the rendering
     * 
     * @return Formatter\Base
     */
    public function getFormatter()
    {
        return $this->formatter;
    }
    
    /**
     * Renders the given node
     * 
     * @param Syntax\Node\Node  $node   Node to render
     * 
     * @return string
     * 
     * @throws Exception
     */
    public function render(Syntax\Node\Node $node)
    {
        //Throw exception if no formatter has been specified
        if (!$this->formatter) {
            throw new \Exception("Formatter not set");
        }
        
        //Reset indentation level
        $this->renderOpts->indLevel = 0;
        
        //Start rendering
        return $this->renderNode($node);
    }
    
    /**
     * Renders a node
     * 
     * @param Syntax\Node\Node  $node   Node to render
     * 
     * @return string
     */
    protected function renderNode(Syntax\Node\Node $node)
    {
        $code = "";
        $type = $node->getType();
        switch ($type) {
            case "ArrayExpression":
            case "ArrayPattern":
                $code .= "[" .
                         $this->joinNodes(
                            $node->getElements(),
                            "," . $this->renderOpts->sao
                         ) .
                         "]";
            break;
            case "ArrowFunctionExpression":
                $code .= "(" .
                         $this->renderOpts->sirb .
                         $this->joinNodes(
                            $node->getParams(),
                            "," . $this->renderOpts->sao
                         ) .
                         $this->renderOpts->sirb .
                         ")" .
                         $this->renderOpts->sao .
                         "=>" .
                         $this->renderStatementBlock($node->getBody(), true);
            break;
            case "AssignmentExpression":
            case "AssignmentPattern":
            case "BinaryExpression":
            case "LogicalExpression":
                $operator = $type === "AssignmentPattern" ?
                            "=" :
                            $node->getOperator();
                $code .= $this->renderNode($node->getLeft()) .
                         $this->renderOpts->sao .
                         $operator .
                         $this->renderOpts->sao .
                         $this->renderNode($node->getRight());
            break;
            case "BlockStatement":
            case "ClassBody":
            case "Program":
                $code .= $this->renderStatementBlock(
                    $node->getBody(), false, false
                );
            break;
            case "BreakStatement":
            case "ContinueStatement":
                $code .= $type === "BreakStatement" ? "break" : "continue";
                if ($label = $node->getLabel()) {
                    $code .= " " . $this->renderNode($label);
                }
            break;
            case "CallExpression":
            case "NewExpression":
                if ($type === "NewExpression") {
                    $code .= "new ";
                }
                $code .= $this->renderNode($node->getCallee()) .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->joinNodes(
                            $node->getArguments(),
                            "," . $this->renderOpts->sao
                         ) .
                         $this->renderOpts->sirb .
                         ")";
            break;
            case "CatchClause":
                $code .= "catch" .
                         $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->renderNode($node->getParam()) .
                         $this->renderOpts->sirb .
                         ")" .
                         $this->renderStatementBlock($node->getBody(), true);
            break;
            case "ClassExpression":
            case "ClassDeclaration":
                $code .= "class";
                if ($id = $node->getId()) {
                    $code .= " " . $this->renderNode($id);
                }
                if ($superClass = $node->getSuperClass()) {
                    $code .= " extends " . $this->renderNode($superClass);
                }
                $code .= $this->renderStatementBlock(
                    $node->getBody(), true, false, false
                );
            break;
            case "ConditionalExpression":
                $code .= $this->renderNode($node->getTest()) .
                         $this->renderOpts->sao .
                         "?" .
                         $this->renderOpts->sao .
                         $this->renderNode($node->getConsequent()) .
                         $this->renderOpts->sao .
                         ":" .
                         $this->renderOpts->sao .
                         $this->renderNode($node->getAlternate());
            break;
            case "DebuggerStatement":
                $code .= "debugger";
            break;
            case "DoWhileStatement":
                $code .= "do" .
                         $this->renderStatementBlock(
                            $node->getBody(), null, true
                         ) .
                         $this->renderOpts->sao .
                         "while" .
                         $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->renderNode($node->getTest()) .
                         $this->renderOpts->sirb .
                         ")";
            break;
            case "EmptyStatement":
            break;
            case "ExportAllDeclaration":
                $code .= "export * from " .
                         $this->renderNode($node->getSource());
            break;
            case "ExportDefaultDeclaration":
                $code .= "export default " .
                         $this->renderNode($node->getDeclaration());
            break;
            case "ExportNamedDeclaration":
                $code .= "export";
                if ($dec = $node->getDeclaration()) {
                    $code .= " " .
                             $this->renderNode($dec);
                } else {
                    $code .= $this->renderOpts->sao .
                             "{" .
                             $this->joinNodes(
                                 $node->getSpecifiers(),
                                 "," . $this->renderOpts->sao
                             ) .
                             "}";
                    if ($source = $node->getSource()) {
                        $code .= $this->renderOpts->sao .
                                 $this->renderNode($source);
                    }
                }
            break;
            case "ExportSpecifier":
            case "ImportSpecifier":
                $local = $this->renderNode($node->getLocal());
                $ref = $this->renderNode(
                    $type === "ExportSpecifier" ?
                    $node->getExported() :
                    $node->getImported()
                );
                $code .= $local === $ref ?
                         $local :
                         $local . " as " . $ref;
            break;
            case "ExpressionStatement":
                $code .= $this->renderNode($node->getExpression());
            break;
            case "ForInStatement":
            case "ForOfStatement":
                $code .= "for" .
                         $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->renderNode($node->getLeft()) .
                         " " . ($type === "ForInStatement" ? "in" : "of") . " " .
                         $this->renderNode($node->getRight()) .
                         $this->renderOpts->sirb .
                         ")" .
                         $this->renderStatementBlock($node->getBody());
            break;
            case "ForStatement":
                $code .= "for" .
                         $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb;
                if ($init = $node->getInit()) {
                    $code .= $this->renderNode($init);
                }
                $code .= ";" . $this->renderOpts->sao;
                if ($test = $node->getTest()) {
                    $code .= $this->renderNode($test);
                }
                $code .= ";" . $this->renderOpts->sao;
                if ($update = $node->getUpdate()) {
                    $code .= $this->renderNode($update);
                }
                $code .= $this->renderOpts->sirb .
                         ")" .
                         $this->renderStatementBlock($node->getBody());
            break;
            case "FunctionDeclaration":
            case "FunctionExpression":
                $id = $node->getId();
                $code .= "function";
                if ($node->getGenerator()) {
                    $code .= $this->renderOpts->sao .
                             "*" .
                             $this->renderOpts->sao;
                } elseif ($id) {
                    $code .= " ";
                }
                if ($id) {
                    $code .= $this->renderNode($id);
                }
                $code .= $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->joinNodes(
                            $node->getParams(),
                            "," . $this->renderOpts->sao
                         ) .
                         $this->renderOpts->sirb .
                         ")" .
                         $this->renderStatementBlock($node->getBody(), true);
            break;
            case "Identifier":
                $code .= $node->getName();
            break;
            case "IfStatement":
                $code .= "if" .
                         $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->renderNode($node->getTest()) .
                         $this->renderOpts->sirb .
                         ")" .
                         $this->renderStatementBlock($node->getConsequent());
                if ($alternate = $node->getAlternate()) {
                    $code .= $this->renderOpts->sao .
                             "else" .
                             $this->renderStatementBlock(
                                 $node->getAlternate(),
                                 null,
                                 true
                             );
                }
            break;
            case "ImportDeclaration":
                $code .= "import ";
                $specifiers = $node->getSpecifiers();
                if (count($specifiers)) {
                    $parts = array();
                    $orderedSpecTypes = array(
                        "ImportDefaultSpecifier",
                        "ImportNamespaceSpecifier",
                        "ImportSpecifier"
                    );
                    foreach ($orderedSpecTypes as $type) {
                        foreach ($specifiers as $spec) {
                            if ($spec->getType() === $type) {
                                $parts[] = $spec;
                            }
                        }
                    }
                    $code .= $this->joinNodes(
                                $parts,
                                "," . $this->renderOpts->sao
                             ) .
                             " from ";
                }
                $code .= $this->renderNode($node->getSource());
            break;
            case "ImportDefaultSpecifier":
                $code .= $this->renderNode($node->getLocal());
            break;
            case "ImportNamespaceSpecifier":
                $code .= "* as " . $this->renderNode($node->getLocal());
            break;
            case "LabeledStatement":
                $code .= $this->renderNode($node->getLabel()) .
                         ":" .
                         $this->renderOpts->nl .
                         $this->getIndentation() .
                         $this->renderNode($node->getBody());
            break;
            case "Literal":
                $code .= $node->getRaw();
            break;
            case "MemberExpression":
                $property = $this->renderNode($node->getProperty());
                $code .= $this->renderNode($node->getObject());
                if ($node->getComputed()) {
                    $code .= "[" . $property . "]";
                } else {
                    $code .= "." . $property;
                }
            break;
            case "MetaProperty":
                $code .= $node->getMeta() . "." . $node->getProperty();
            break;
            case "MethodDefinition":
                if ($node->getStatic()) {
                    $code .= "static ";
                }
                $value = $node->getValue();
                $key = $node->getKey();
                $kind = $node->getKind();
                if ($kind === $node::KIND_GET || $kind === $node::KIND_SET) {
                    $code .= $kind . " ";
                } elseif ($value->getGenerator()) {
                    $code .= "*" .
                             $this->renderOpts->sao;
                }
                if ($node->getComputed()) {
                    $code .= "[" .
                             $this->renderNode($key) .
                             "]";
                } else {
                    $code .= $this->renderNode($key);
                }
                $code .= $this->renderOpts->sao .
                         preg_replace("/^[^\(]+/", "", $this->renderNode($value));
            break;
            case "ObjectExpression":
            case "ObjectPattern":
                $code .= "{" .
                         $this->joinNodes(
                            $node->getProperties(),
                            "," . $this->renderOpts->sao
                         ) .
                         "}";
            break;
            case "ParenthesizedExpression":
                $code .= "(" .
                         $this->sirb .
                         $this->renderNode($node->getExpression()) .
                         $this->sirb .
                         ")";
            break;
            case "Property":
                $value = $node->getValue();
                $key = $node->getKey();
                $kind = $node->getKind();
                if ($kind === $node::KIND_GET || $kind === $node::KIND_SET) {
                    $code .= $kind . " ";
                } elseif ($value->getType() === "FunctionExpression" &&
                          $value->getGenerator()) {
                    $code .= "*" .
                             $this->renderOpts->sao;
                }
                $compiledKey = $this->renderNode($key);
                $compiledValue = $this->renderNode($value);
                if ($node->getComputed()) {
                    $code .= "[" . $compiledKey . "]";
                } else {
                    $code .= $compiledKey;
                }
                if ($node->getMethod()) {
                    $code .= $this->renderOpts->sao .
                             preg_replace("/^[^\(]+/", "", $compiledValue);
                } elseif ($compiledKey !== $compiledValue) {
                    $code .= ($node->getShorthand() ? "=" : ":") .
                             $this->renderOpts->sao .
                             $compiledValue;
                }
            break;
            case "RegExpLiteral":
                $code .= $node->getRaw();
            break;
            case "RestElement":
            case "SpreadElement":
                $code .= "..." . $this->renderNode($node->getArgument());
            break;
            case "ReturnStatement":
                $code .= "return";
                if ($argument = $node->getArgument()) {
                    $code .= " " . $this->renderNode($argument);
                }
            break;
            case "SequenceExpression":
                $code .= $this->joinNodes(
                            $node->getExpressions(),
                            "," . $this->renderOpts->sao
                         );
            break;
            case "Super":
                $code .= "super";
            break;
            case "SwitchCase":
                if ($test = $node->getTest()) {
                    $source = "case " . $this->renderNode($test);
                } else {
                    $code .= "default";
                }
                $code .= ":" .
                         $this->renderOpts->nl .
                         $this->renderStatementBlock(
                             $node->getConsequent(),
                             false
                         );
            break;
            case "SwitchStatement":
                $code .= "switch" .
                         $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->renderNode($node->getDiscriminant()) .
                         $this->renderOpts->sirb .
                         ")" .
                         $this->renderStatementBlock(
                             $node->getCases(), true, false, false
                         );
            break;
            case "TaggedTemplateExpression":
                $code .= $this->renderNode($node->getTag()) .
                         $this->renderNode($node->getQuasi());
            break;
            case "TemplateElement":
                $code .= $node->getRawValue();
            break;
            case "TemplateLiteral":
                $code .= "`";
                foreach ($node->getParts() as $part) {
                    if ($part->getType() === "TemplateElement") {
                        $code .= $this->renderNode($part);
                    } else {
                        $code .= "$" . "{" . $this->renderNode($part) . "}";
                    }
                }
                $code .= "`";
            break;
            case "ThisExpression":
                $code .= "this";
            break;
            case "ThrowStatement":
                $code .= "throw " . $this->renderNode($node->getArgument());
            break;
            case "TryStatement":
                $code .= "try" .
                         $this->renderStatementBlock($node->getBlock(), true);
                if ($handler = $node->getHandler()) {
                    $code .= $this->renderOpts->sao .
                             $this->renderNode($handler);
                }
                if ($finalizer = $node->getFinalizer()) {
                    $code .= $this->renderOpts->sao .
                             "finally" .
                             $this->renderStatementBlock($finalizer, true);
                }
            break;
            case "UnaryExpression":
            case "UpdateExpression":
                $prefix = $node->getPrefix();
                if ($prefix) {
                    $code .= $node->getOperator();
                }
                $code .= $this->renderNode($node->getArgument());
                if (!$prefix) {
                    $code .= $node->getOperator();
                }
            break;
            case "VariableDeclaration":
                $this->renderOpts->indLevel++;
                $indentation = $this->getIndentation();
                $code .= $node->getKind() .
                         " " .
                         $this->joinNodes(
                            $node->getDeclarations(),
                            "," . $this->renderOpts->nl . $indentation
                         );
                $this->renderOpts->indLevel--;
            break;
            case "VariableDeclarator":
                $code .= $this->renderNode($node->getId());
                if ($init = $node->getInit()) {
                    $code .= $this->renderOpts->sao .
                             "=" .
                             $this->renderOpts->sao .
                             $this->renderNode($init);
                }
            break;
            case "WhileStatement":
                $code .= "while" .
                         $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->renderNode($node->getTest()) .
                         $this->renderOpts->sirb .
                         ")" .
                         $this->renderStatementBlock($node->getBody());
            break;
            case "WithStatement":
                $code .= "with" .
                         $this->renderOpts->sao .
                         "(" .
                         $this->renderOpts->sirb .
                         $this->renderNode($node->getObject()) .
                         $this->renderOpts->sirb .
                         ")" .
                         $this->renderStatementBlock($node->getBody());
            break;
            case "YieldExpression":
                $code .= "yield";
                if ($node->getDelegate()) {
                    $code .= " *";
                }
                if ($argument = $node->getArgument()) {
                    $code .= " " . $this->renderNode($argument);
                }
            break;
        }
        return $code;
    }
    
    /**
     * Renders a node as a block statement
     * 
     * @param Syntax\Node\Node|array    $node               Node or array of 
     *                                                      nodes to render
     * @param bool                      $forceBrackets      Overrides brackets
     *                                                      inserting rules
     * @param bool                      $mandatorySeparator True if a starting
     *                                                      separator is
     *                                                      mandatory
     * @param bool                      $addSemicolons      Semicolons are
     *                                                      inserted autmatically
     *                                                      if this parameter is
     *                                                      not false
     * 
     * @return string
     */
    protected function renderStatementBlock(
        $node, $forceBrackets = null, $mandatorySeparator = false,
        $addSemicolons = true
    ) {
        //Special handling of BlockStatement and ClassBody nodes by rendering
        //their child nodes
        if (!is_array($node) &&
            in_array($node->getType(), array("BlockStatement", "ClassBody"))) {
            $node = $node->getBody();
        }
        
        //If $forceBrackets is not null use its value to override curly brackets
        //insertion rules
        if ($forceBrackets !== null) {
            $hasBrackets = $forceBrackets;
        } else {
            //Insert curly brackets if required by the formatter or if the
            //number of nodes to render is different from one
            $hasBrackets = $this->renderOpts->awb ||
                           (is_array($node) && count($node) !== 1);
        }
        $currentIndentation = $this->getIndentation();
        
        //If $forceBrackets is not set to false then the node can be wrapped in
        //curly braces, so a separator defined by formatter must be inserted
        if ($forceBrackets !== false) {
            if ($this->renderOpts->nlbc) {
                $code .= $this->renderOpts->nl . $currentIndentation;
            } else {
                $code .= $this->renderOpts->sao;
            }
        }
        
        //Insert open curly bracket if required
        if ($hasBrackets) {
            $code .= "{" . $this->renderOpts->nl;
        } elseif ($mandatorySeparator) {
            //If bracket is not inserted but a separator is still required
            //a space is added
            $code .= " ";
        }
        
        //Increase indentation level for sub nodes if  $forceBrackets is not
        //false
        if ($forceBrackets !== false) {
            $this->renderOpts->indLevel++;
        }
        $subIndentation = $this->getIndentation();
        
        //Render the node or the array of nodes
        $emptyBody = is_array($node) && !count($node);
        if (is_array($node)) {
            if (!$emptyBody) {
                $code .= $subIndentation .
                         $this->joinNodes(
                            $node,
                            $this->renderOpts->nl . $subIndentation,
                            $addSemicolons
                         );
            }
        } else {
            $code .= $subIndentation . $this->renderNode($node);
            if ($addSemicolons &&
                !in_array($node->getType(), $this->noSemicolon)) {
                $code .= ";";
            }
        }
        
        //If  $forceBrackets is not false reset the indentation level
        if ($forceBrackets !== false) {
            $this->renderOpts->indLevel--;
        }
        
        //Insert closing curly bracket if required
        if ($hasBrackets) {
            //Add a new line if something was rendered
            if (!$emptyBody) {
                $code .= $this->renderOpts->nl;
            }
            $code .= $currentIndentation . "}";
        }
        
        return $code;
    }
    
    /**
     * Joins an array of nodes with the given separator
     * 
     * @param array     $nodes          Nodes
     * @param string    $separator      Separator
     * @param bool      $addSemicolons  True to add semicolons after each node
     * 
     * @return string
     */
    protected function joinNodes($nodes, $separator, $addSemicolons=false)
    {
        $parts = array();
        foreach ($nodes as $node) {
            if (!$node) {
                $code = "";
            } else {
                $code = $this->renderNode($node);
                if ($addSemicolons &&
                    !in_array($node->getType(), $this->noSemicolon)) {
                    $code .= ";";
                }
            }
            $parts[] = $code;
        }
        return implode($separator, $parts);
    }
    
    
    
    /**
     * Returns the current indentation string
     * 
     * @return string
     */
    protected function getIndentation()
    {
        return str_repeat(
            $this->renderOpts->ind,
            $this->renderOpts->indLevel
        );
    }
}