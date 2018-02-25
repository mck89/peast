<?php
namespace Peast\test\Traverser;

use \Peast\Traverser;
use \Peast\Syntax\Node;

class TraverserTest extends \Peast\test\TestBase
{
    public function testModifyNodes()
    {
        $source = "var a = 1, b = 'c', d = false, e = [], f = /foo/;";
        $tree = \Peast\Peast::latest($source)
                ->parse()
                ->traverse(function($node) {
                    if ($node->getType() === "Literal") {
                        if ($node instanceof Node\NumericLiteral) {
                            $node->setValue(2);
                        } elseif ($node instanceof Node\StringLiteral) {
                            $node->setValue("test");
                        } elseif ($node instanceof Node\BooleanLiteral) {
                            $node->setValue(true);
                        }
                    } elseif ($node->getType() === "ArrayExpression") {
                        $literal = new Node\NumericLiteral();
                        $literal->setValue(1);
                        $node->setElements(array($literal));
                    } elseif ($node->getType() === "RegExpLiteral") {
                        $node->setValue("/bar/");
                    }
                });
        $body = $tree->getBody();
        $declarations = $body[0]->getDeclarations();
        $this->assertEquals(2, $declarations[0]->getInit()->getValue());
        $this->assertEquals("test", $declarations[1]->getInit()->getValue());
        $this->assertEquals(true, $declarations[2]->getInit()->getValue());
        $this->assertEquals(1, count($declarations[3]->getInit()->getElements()));
        $this->assertEquals("/bar/", $declarations[4]->getInit()->getValue());
    }
    
    public function testRemoveNodesAndActions()
    {
        $source = "var a = 1, b = [2, [2], 3, 2], c = 1;";
        $tree = \Peast\Peast::latest($source)
                ->parse()
                ->traverse(function($node) {
                    if ($node->getType() === "Literal") {
                        $value = $node->getValue();
                        if ($value <= 2) {
                            return Traverser::REMOVE_NODE;
                        } else {
                            return Traverser::REMOVE_NODE | Traverser::STOP_TRAVERSING;
                        }
                    } elseif ($node->getType() === "ArrayExpression") {
                        if (count($node->getElements()) === 1) {
                            return Traverser::DONT_TRAVERSE_CHILD_NODES;
                        }
                    }
                });
        $body = $tree->getBody();
        $declarations = $body[0]->getDeclarations();
        $this->assertEquals(null, $declarations[0]->getInit());
        $arrayElements = $declarations[1]->getInit()->getElements();
        $this->assertEquals(2, count($arrayElements));
        $this->assertEquals(1, count($arrayElements[0]->getElements()));
        $this->assertEquals(1, $declarations[2]->getInit()->getValue());
    }
    
    public function testReplaceNodesAndActions()
    {
        $source = "var a, b = 1, c = [2], d = 3, e = 4;";
        $tree = \Peast\Peast::latest($source)
                ->parse()
                ->traverse(function($node) {
                    if ($node->getType() === "Literal") {
                        $value = $node->getValue();
                        if ($value <= 2) {
                            return new \Peast\Syntax\Node\ArrayExpression;
                        } else {
                            return array(
                                new \Peast\Syntax\Node\ArrayExpression,
                                Traverser::STOP_TRAVERSING
                            );
                        }
                    } elseif ($node->getType() === "ArrayExpression") {
                        $replacement = new \Peast\Syntax\Node\ArrayExpression;
                        $replacement->setElements($node->getElements());
                        return array(
                            $replacement,
                            Traverser::DONT_TRAVERSE_CHILD_NODES
                        );
                    }
                });
        $body = $tree->getBody();
        $declarations = $body[0]->getDeclarations();
        $this->assertEquals(null, $declarations[0]->getInit());
        $this->assertEquals("ArrayExpression", $declarations[1]->getInit()->getType());
        $this->assertEquals("ArrayExpression", $declarations[2]->getInit()->getType());
        $arrayElements = $declarations[2]->getInit()->getElements();
        $this->assertEquals(1, count($arrayElements));
        $this->assertEquals("Literal", $arrayElements[0]->getType());
        $this->assertEquals("ArrayExpression", $declarations[3]->getInit()->getType());
        $this->assertEquals("Literal", $declarations[4]->getInit()->getType());
    }
    
    
    public function testArrayWithNullElements()
    {
        $source = "var a = [b,,c,,d,]";
        $names = array();
        \Peast\Peast::latest($source)
            ->parse()
            ->traverse(function($node) use (&$names) {
                if ($node->getType() === "Identifier") {
                    $names[] = $node->getName();
                }
            });
        $this->assertEquals(array("a", "b", "c", "d"), $names);
    }
    
    public function testTraverseTemplate()
    {
        $source = '`foo${exp()}bar`';
        $types = array();
        \Peast\Peast::latest($source)
            ->parse()
            ->traverse(function($node) use (&$types) {
                $type = $node->getType();
                $types[] = $type;
            });
        $this->assertEquals(
            array(
                "Program", "ExpressionStatement", "TemplateLiteral",
                "TemplateElement", "CallExpression", "Identifier",
                "TemplateElement"
            ),
            $types
        );
    }
}