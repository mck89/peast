<?php
namespace Peast\test;

use Peast\Syntax\Node\Expression;
use Peast\Syntax\Node\Declaration;
use Peast\Syntax\Node\Pattern;
use Peast\Syntax\Node\Statement;

class QueryTest extends TestBase
{
    protected function getTree()
    {
        $source = "
            var a = 1, b = 2, c = 3;
            var d = 4, e = 5, f = 6;
            
            if (a) {
                if (b) {
                    if (c) {
                        call1(a, b, c);
                    }
                }
            }
            if (d) {
                if (e) {
                    if (f) {
                        call2(d, e, f);
                    }
                }
            }
            var g = 7, h = 8, i = 9;
            
            if (g) {
                if (h) {
                    if (i) {
                        call3(g, h, i);
                    }
                }
            }
            
            function call1(x, xx, xxx) {
                return \"That's a string\";
            }
            function call2(y, yy, yyy) {
                return [-1.2343e+2, 0xFFEF, 0o7766, 0b111101101];
            }
            function call3(z, zz, zzz) {
                return true || false || (true && null);
            }
            
            arr = [
                1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
                11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
                21, 22, 23, 24, 25, 26, 27, 28, 29, 30
            ];
        ";
        return \Peast\Peast::latest($source)->parse();
    }

    public function selectorsProvider()
    {
        return array(
            array(
                "LabeledStatement", 0, "empty result", null
            ),
            array(
                "VariableDeclaration", 3,
                "selector type",
                array(
                    array("VariableDeclaration", "a"),
                    array("VariableDeclaration", "d"),
                    array("VariableDeclaration", "g"),
                )
            ),
            array(
                "VariableDeclaration VariableDeclarator", 9,
                "combinator descendant",
                array(
                    array("VariableDeclarator", "a"),
                    array("VariableDeclarator", "b"),
                    array("VariableDeclarator", "c"),
                    array("VariableDeclarator", "d"),
                    array("VariableDeclarator", "e"),
                    array("VariableDeclarator", "f"),
                    array("VariableDeclarator", "g"),
                    array("VariableDeclarator", "h"),
                    array("VariableDeclarator", "i")
                )
            ),
            array(
                "FunctionDeclaration > Identifier", 12,
                "combinator children",
                array(
                    array("Identifier", "call1"),
                    array("Identifier", "x"),
                    array("Identifier", "xx"),
                    array("Identifier", "xxx"),
                    array("Identifier", "call2"),
                    array("Identifier", "y"),
                    array("Identifier", "yy"),
                    array("Identifier", "yyy"),
                    array("Identifier", "call3"),
                    array("Identifier", "z"),
                    array("Identifier", "zz"),
                    array("Identifier", "zzz")
                )
            ),
            array(
                "FunctionDeclaration, VariableDeclaration, FunctionDeclaration", 6,
                "groups",
                array(
                    array("VariableDeclaration", "a"),
                    array("FunctionDeclaration", "call1"),
                    array("FunctionDeclaration", "call2"),
                    array("FunctionDeclaration", "call3"),
                    array("VariableDeclaration", "d"),
                    array("VariableDeclaration", "g"),
                ),
                true
            ),
            array(
                "Identifier[name='xxx']", 1,
                "selector attr equals",
                array(
                    array("Identifier", "xxx")
                )
            ),
            array(
                "[id.name='call1']", 1,
                "selector multi attr equals",
                array(
                    array("FunctionDeclaration", "call1")
                )
            ),
            array(
                "[id.name='CALL1' i]", 1,
                "selector multi attr equals case insensitive",
                array(
                    array("FunctionDeclaration", "call1")
                )
            ),
            array(
                "FunctionDeclaration[id.name]", 3,
                "selector multi attr exists",
                array(
                    array("FunctionDeclaration", "call1"),
                    array("FunctionDeclaration", "call2"),
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "FunctionDeclaration[id.name^='call']", 3,
                "selector multi attr begins with",
                array(
                    array("FunctionDeclaration", "call1"),
                    array("FunctionDeclaration", "call2"),
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "FunctionDeclaration[id.name^='CALL' i]", 3,
                "selector multi attr begins with case insensitive",
                array(
                    array("FunctionDeclaration", "call1"),
                    array("FunctionDeclaration", "call2"),
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "FunctionDeclaration[id.name*='all']", 3,
                "selector multi attr contains",
                array(
                    array("FunctionDeclaration", "call1"),
                    array("FunctionDeclaration", "call2"),
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "FunctionDeclaration[id.name*='ALL' i]", 3,
                "selector multi attr contains case insensitive",
                array(
                    array("FunctionDeclaration", "call1"),
                    array("FunctionDeclaration", "call2"),
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "FunctionDeclaration[id.name$='ll3']", 1,
                "selector multi attr ends with",
                array(
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "FunctionDeclaration[id.name$='LL3' i]", 1,
                "selector multi attr ends with case insensitive",
                array(
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "FunctionDeclaration[id.name=/call\d+/]", 3,
                "selector multi attr regex",
                array(
                    array("FunctionDeclaration", "call1"),
                    array("FunctionDeclaration", "call2"),
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "FunctionDeclaration[id.name=/CALL\d+/i]", 3,
                "selector multi attr regex case insensitive",
                array(
                    array("FunctionDeclaration", "call1"),
                    array("FunctionDeclaration", "call2"),
                    array("FunctionDeclaration", "call3")
                )
            ),
            array(
                "Literal[value='That\'s a string']", 1,
                "selector attr equals escaped",
                array(
                    array("Literal", "That's a string")
                )
            ),
            array(
                "Literal[value=true]", 2,
                "selector attr boolean type true",
                array(
                    array("Literal", true),
                    array("Literal", true)
                )
            ),
            array(
                "[value=false]Literal", 1,
                "selector attr boolean type false",
                array(
                    array("Literal", false)
                )
            ),
            array(
                "Literal[value=null]", 1,
                "selector attr null",
                array(
                    array("Literal", null)
                )
            ),
            array(
                "Literal[value=20]", 1,
                "selector attr int",
                array(
                    array("Literal", 20)
                )
            ),
            array(
                "UnaryExpression[operator='-'] Literal[value=1.2343e+2]", 1,
                "selector attr float",
                array(
                    array("Literal", 1.2343e+2)
                )
            ),
            array(
                "Literal[value=0xFFEF]", 1,
                "selector attr hex",
                array(
                    array("Literal", 0xFFEF)
                )
            ),
            array(
                "Literal[value=0o7766]", 1,
                "selector attr octal",
                array(
                    array("Literal", 07766)
                )
            ),
            array(
                "Literal[value=0b111101101]", 1,
                "selector attr binary",
                array(
                    array("Literal", bindec("111101101"))
                )
            ),
            array(
                "Literal[value>10][value<20]", 9,
                "selector attr greater/lower",
                array(
                    array("Literal", 11),
                    array("Literal", 12),
                    array("Literal", 13),
                    array("Literal", 14),
                    array("Literal", 15),
                    array("Literal", 16),
                    array("Literal", 17),
                    array("Literal", 18),
                    array("Literal", 19),
                )
            ),
            array(
                "Literal[value>=20][value<=30]", 11,
                "selector attr greater equals/lower equals",
                array(
                    array("Literal", 20),
                    array("Literal", 21),
                    array("Literal", 22),
                    array("Literal", 23),
                    array("Literal", 24),
                    array("Literal", 25),
                    array("Literal", 26),
                    array("Literal", 27),
                    array("Literal", 28),
                    array("Literal", 29),
                    array("Literal", 30),
                )
            ),
            array(
                "VariableDeclaration + VariableDeclaration", 1,
                "combinator adjacent sibling",
                array(
                    array("VariableDeclaration", "d"),
                )
            ),
            array(
                "VariableDeclaration ~ VariableDeclaration", 2,
                "combinator general sibling",
                array(
                    array("VariableDeclaration", "d"),
                    array("VariableDeclaration", "g")
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :first-child", 1,
                "selector pseudo first child",
                array(
                    array("Literal", 1)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :last-child", 1,
                "selector pseudo last child",
                array(
                    array("Literal", 30)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(even)", 15,
                "selector pseudo nth-child even",
                array(
                    array("Literal", 2),
                    array("Literal", 4),
                    array("Literal", 6),
                    array("Literal", 8),
                    array("Literal", 10),
                    array("Literal", 12),
                    array("Literal", 14),
                    array("Literal", 16),
                    array("Literal", 18),
                    array("Literal", 20),
                    array("Literal", 22),
                    array("Literal", 24),
                    array("Literal", 26),
                    array("Literal", 28),
                    array("Literal", 30),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(odd)", 15,
                "selector pseudo nth-child odd",
                array(
                    array("Literal", 1),
                    array("Literal", 3),
                    array("Literal", 5),
                    array("Literal", 7),
                    array("Literal", 9),
                    array("Literal", 11),
                    array("Literal", 13),
                    array("Literal", 15),
                    array("Literal", 17),
                    array("Literal", 19),
                    array("Literal", 21),
                    array("Literal", 23),
                    array("Literal", 25),
                    array("Literal", 27),
                    array("Literal", 29),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(3n+4)", 9,
                "selector pseudo nth-child 3n+4",
                array(
                    array("Literal", 4),
                    array("Literal", 7),
                    array("Literal", 10),
                    array("Literal", 13),
                    array("Literal", 16),
                    array("Literal", 19),
                    array("Literal", 22),
                    array("Literal", 25),
                    array("Literal", 28)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(5n+6)", 5,
                "selector pseudo nth-child 5n+6",
                array(
                    array("Literal", 6),
                    array("Literal", 11),
                    array("Literal", 16),
                    array("Literal", 21),
                    array("Literal", 26)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(4n)", 7,
                "selector pseudo nth-child 4n",
                array(
                    array("Literal", 4),
                    array("Literal", 8),
                    array("Literal", 12),
                    array("Literal", 16),
                    array("Literal", 20),
                    array("Literal", 24),
                    array("Literal", 28)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(7)", 1,
                "selector pseudo nth-child 7",
                array(
                    array("Literal", 7)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(n)", 30,
                "selector pseudo nth-child n",
                array(
                    array("Literal", 1),
                    array("Literal", 2),
                    array("Literal", 3),
                    array("Literal", 4),
                    array("Literal", 5),
                    array("Literal", 6),
                    array("Literal", 7),
                    array("Literal", 8),
                    array("Literal", 9),
                    array("Literal", 10),
                    array("Literal", 11),
                    array("Literal", 12),
                    array("Literal", 13),
                    array("Literal", 14),
                    array("Literal", 15),
                    array("Literal", 16),
                    array("Literal", 17),
                    array("Literal", 18),
                    array("Literal", 19),
                    array("Literal", 20),
                    array("Literal", 21),
                    array("Literal", 22),
                    array("Literal", 23),
                    array("Literal", 24),
                    array("Literal", 25),
                    array("Literal", 26),
                    array("Literal", 27),
                    array("Literal", 28),
                    array("Literal", 29),
                    array("Literal", 30),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(n+20)", 11,
                "selector pseudo nth-child n+20",
                array(
                    array("Literal", 20),
                    array("Literal", 21),
                    array("Literal", 22),
                    array("Literal", 23),
                    array("Literal", 24),
                    array("Literal", 25),
                    array("Literal", 26),
                    array("Literal", 27),
                    array("Literal", 28),
                    array("Literal", 29),
                    array("Literal", 30),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(-n+5)", 5,
                "selector pseudo nth-child -n+5",
                array(
                    array("Literal", 1),
                    array("Literal", 2),
                    array("Literal", 3),
                    array("Literal", 4),
                    array("Literal", 5),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-child(-n)", 0,
                "selector pseudo nth-child -n", null
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(even)", 15,
                "selector pseudo nth-last-child even",
                array(
                    array("Literal", 1),
                    array("Literal", 3),
                    array("Literal", 5),
                    array("Literal", 7),
                    array("Literal", 9),
                    array("Literal", 11),
                    array("Literal", 13),
                    array("Literal", 15),
                    array("Literal", 17),
                    array("Literal", 19),
                    array("Literal", 21),
                    array("Literal", 23),
                    array("Literal", 25),
                    array("Literal", 27),
                    array("Literal", 29),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(odd)", 15,
                "selector pseudo nth-last-child odd",
                array(
                    array("Literal", 2),
                    array("Literal", 4),
                    array("Literal", 6),
                    array("Literal", 8),
                    array("Literal", 10),
                    array("Literal", 12),
                    array("Literal", 14),
                    array("Literal", 16),
                    array("Literal", 18),
                    array("Literal", 20),
                    array("Literal", 22),
                    array("Literal", 24),
                    array("Literal", 26),
                    array("Literal", 28),
                    array("Literal", 30),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(3n+4)", 9,
                "selector pseudo nth-last-child 3n+4",
                array(
                    array("Literal", 3),
                    array("Literal", 6),
                    array("Literal", 9),
                    array("Literal", 12),
                    array("Literal", 15),
                    array("Literal", 18),
                    array("Literal", 21),
                    array("Literal", 24),
                    array("Literal", 27)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(5n+6)", 5,
                "selector pseudo nth-last-child 5n+6",
                array(
                    array("Literal", 5),
                    array("Literal", 10),
                    array("Literal", 15),
                    array("Literal", 20),
                    array("Literal", 25)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(4n)", 7,
                "selector pseudo nth-last-child 4n",
                array(
                    array("Literal", 3),
                    array("Literal", 7),
                    array("Literal", 11),
                    array("Literal", 15),
                    array("Literal", 19),
                    array("Literal", 23),
                    array("Literal", 27)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(7)", 1,
                "selector pseudo nth-last-child 7",
                array(
                    array("Literal", 24)
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(n)", 30,
                "selector pseudo nth-last-child n",
                array(
                    array("Literal", 1),
                    array("Literal", 2),
                    array("Literal", 3),
                    array("Literal", 4),
                    array("Literal", 5),
                    array("Literal", 6),
                    array("Literal", 7),
                    array("Literal", 8),
                    array("Literal", 9),
                    array("Literal", 10),
                    array("Literal", 11),
                    array("Literal", 12),
                    array("Literal", 13),
                    array("Literal", 14),
                    array("Literal", 15),
                    array("Literal", 16),
                    array("Literal", 17),
                    array("Literal", 18),
                    array("Literal", 19),
                    array("Literal", 20),
                    array("Literal", 21),
                    array("Literal", 22),
                    array("Literal", 23),
                    array("Literal", 24),
                    array("Literal", 25),
                    array("Literal", 26),
                    array("Literal", 27),
                    array("Literal", 28),
                    array("Literal", 29),
                    array("Literal", 30),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(n+20)", 11,
                "selector pseudo nth-last-child n+20",
                array(
                    array("Literal", 1),
                    array("Literal", 2),
                    array("Literal", 3),
                    array("Literal", 4),
                    array("Literal", 5),
                    array("Literal", 6),
                    array("Literal", 7),
                    array("Literal", 8),
                    array("Literal", 9),
                    array("Literal", 10),
                    array("Literal", 11),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(-n+5)", 5,
                "selector pseudo nth-last-child -n+5",
                array(
                    array("Literal", 26),
                    array("Literal", 27),
                    array("Literal", 28),
                    array("Literal", 29),
                    array("Literal", 30),
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :nth-last-child(-n)", 0,
                "selector pseudo nth-last-child -n", null
            ),
            array(
                "AssignmentExpression > ArrayExpression > :not([value<29])", 2,
                "selector pseudo not filter",
                array(
                    array("Literal", 29),
                    array("Literal", 30),
                )
            ),
            array(
                "FunctionDeclaration:not(>[name='call2'],>[name='call3'])", 1,
                "selector pseudo not combinator",
                array(
                    array("FunctionDeclaration", 'call1')
                )
            ),
            array(
                "AssignmentExpression > ArrayExpression > :is([value>=29])", 2,
                "selector pseudo is filter",
                array(
                    array("Literal", 29),
                    array("Literal", 30),
                )
            ),
            array(
                "FunctionDeclaration:is(>[name='call2'],>[name='call3'])", 2,
                "selector pseudo is combinator",
                array(
                    array("FunctionDeclaration", 'call2'),
                    array("FunctionDeclaration", 'call3')
                )
            ),
            array(
                "FunctionDeclaration:has([name='call2'], [name='call3'])", 2,
                "selector pseudo has filter",
                array(
                    array("FunctionDeclaration", 'call2'),
                    array("FunctionDeclaration", 'call3')
                )
            ),
            array(
                "FunctionDeclaration:has(>[name='call1'])", 1,
                "selector pseudo has combinator",
                array(
                    array("FunctionDeclaration", 'call1')
                )
            ),
            array(
                ">FunctionDeclaration ReturnStatement>ArrayExpression>:nth-child(2)", 1,
                "complex selector",
                array(
                    array("Literal", 0xFFEF)
                )
            ),
        );
    }

    /**
     * @dataProvider selectorsProvider
     */
    public function testSelector($selector, $count, $msg, $test, $sort = false)
    {
        $q = $this->getTree()->query($selector);
        $this->assertEquals($count, count($q), "Count $msg");
        if ($count) {
            $checks = array();
            foreach ($q as $node) {
                $type = $node->getType();
                switch ($type) {
                    case "VariableDeclaration":
                        $val = $node->getDeclarations()[0]->getId()->getName();
                        break;
                    case "FunctionDeclaration":
                    case "VariableDeclarator":
                        $val = $node->getId()->getName();
                    break;
                    case "Identifier":
                        $val = $node->getName();
                    break;
                    case "Literal":
                        $val = $node->getValue();
                    break;
                    default:
                        throw new \Exception("Unexpected node $type");
                }
                $checks[] = array($type, $val);
            }
            if ($sort) {
                usort($checks, function ($c1, $c2) {
                    if ($c1[1] === $c2[1]) {
                        return 0;
                    }
                    return $c1[1] < $c2[1] ? -1 : 1;
                });
            }
            $this->assertEquals($test, $checks, "Test $msg");
        }
    }

    public function testPseudoInterface()
    {
        $expected = array(
            "pattern" => 0,
            "statement" => 0,
            "expression" => 0,
            "declaration" => 0
        );
        $this->getTree()->traverse(function ($node) use (&$expected) {
            if ($node instanceof Pattern) {
                $expected["pattern"]++;
            }
            if ($node instanceof Statement) {
                $expected["statement"]++;
            }
            if ($node instanceof Expression) {
                $expected["expression"]++;
            }
            if ($node instanceof Declaration) {
                $expected["declaration"]++;
            }
        });
        foreach ($expected as $selector => $count) {
            $realCount = count($this->getTree()->query(":$selector"));
            $this->assertNotEquals(0, $realCount, "Selector :$selector not 0");
            $this->assertEquals($count, $realCount, "Selector :$selector");
        }
    }

    public function testQueryObjectMethods()
    {
        $q = $this->getTree()->query("AssignmentExpression");
        $q->find("> ArrayExpression > Literal");
        $q->filter("[value>5]");
        $q->filter("[value<8]");
        $this->assertEquals(2, count($q));
        $this->assertEquals(6, $q->get(0)->getValue());
        $this->assertEquals(7, $q->get(1)->getValue());
    }

    public function testEncoding()
    {
        if (function_exists("mb_convert_encoding")) {
            $code = mb_convert_encoding("'à'", "UTF-16", "UTF-8");
            $sel = mb_convert_encoding("[value='à']", "UTF-16", "UTF-8");
            $tree = \Peast\Peast::latest($code, array("sourceEncoding" => "UTF-16"))->parse();
            $q = $tree->query($sel, array("encoding" => "UTF-16"));
            $this->assertEquals(1, count($q));
        }
    }

    public function testInvalidIndex()
    {
        $this->expectException('Exception');
        $q = $this->getTree()->query("FunctionDeclaration");
        $q->get(1000);
    }

    public function invalidSelectorsProvider()
    {
        return array(
            array(""),
            array(">"),
            array("Literal>"),
            array("FunctionDeclaration>>Literal"),
            array("&"),
            array("[value"),
            array("[]"),
            array("[value.]"),
            array("[value=]"),
            array("[value+=1]"),
            array("[value^^1]"),
            array("[value='val]"),
            array("[value=/val]"),
            array("[value$=/val/]"),
            array("[value=invalid]"),
            array("[value=1 i]"),
            array(":first-child()"),
            array(":nth-child"),
            array(":nth-child("),
            array(":nth-child(even"),
            array(":nth-child()"),
            array(":nth-child(abc)"),
            array(":not"),
            array(":not("),
            array(":not()"),
            array(":not(Literal>)"),
            array(":invalid"),
        );
    }

    /**
     * @dataProvider invalidSelectorsProvider
     */
    public function testInvalidSelectors($selector)
    {
        $this->expectException('Peast\Selector\Exception');
        $this->getTree()->query($selector);
    }
}