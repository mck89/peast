<?php
namespace Peast\test;


class QueryTest extends TestBase
{
    private $tree;

    protected function setUp()
    {
        $source = "
            var a = 1, b = 2, c = 3;
            var d = 4, e = 5, f = 6;
            var g = 7, h = 8, i = 9;
            
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
                return 2;
            }
            function call3(z, zz, zzz) {
                return 3;
            }
            
            arr = [
                1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
                11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
                21, 22, 23, 24, 25, 26, 27, 28, 29, 30
            ];
        ";
        $this->tree = \Peast\Peast::latest($source)->parse();
    }

    protected function tearDown()
    {
        $this->tree = null;
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
        );
    }

    /**
     * @dataProvider selectorsProvider
     */
    public function testSelector($selector, $count, $msg, $test, $sort = false)
    {
        $q = $this->tree->query($selector);
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

    //@TODO wrong selectors
    //@TODO attr different value types (int, float, bool, null)
    //@TODO attr "<", ">", "<=", ">="
    //@TODO combinators + ~
    //@TODO pseudo
    //@TODO encoding
    //@TODO complex
    //@TODO filter
    //@TODO sub find
    //@TODO selector begins with combinator
}