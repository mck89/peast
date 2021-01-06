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

    public function testSelectorEmptyResult()
    {
        $q = $this->tree->query("LabeledStatement");
        $this->assertEquals(0, count($q));
    }

    public function testSelectorType()
    {
        $q = $this->tree->query("VariableDeclaration");
        $this->assertEquals(3, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getDeclarations()[0]->getId()->getName();
        }
        $this->assertEquals(array("a", "d", "g"), $checks);
    }

    public function testCombinatorDescendant()
    {
        $q = $this->tree->query("VariableDeclaration VariableDeclarator");
        $this->assertEquals(9, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getId()->getName();
        }
        $this->assertEquals(
            array("a", "b", "c", "d", "e", "f", "g", "h", "i"),
            $checks
        );
    }

    public function testCombinatorChildren()
    {
        $q = $this->tree->query("FunctionDeclaration > Identifier");
        $this->assertEquals(12, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getName();
        }
        //This should get function name and arguments
        $this->assertEquals(
            array("call1", "x", "xx", "xxx", "call2", "y", "yy", "yyy", "call3", "z", "zz", "zzz"),
            $checks
        );
    }

    public function testGroups()
    {
        $q = $this->tree->query("FunctionDeclaration, VariableDeclaration, FunctionDeclaration");
        $this->assertEquals(6, count($q));
        $checks = array();
        foreach ($q as $node) {
            if ($node->getType() === "VariableDeclaration") {
                $checks[] = $node->getDeclarations()[0]->getId()->getName();
            } else {
                $checks[] = $node->getId()->getName();
            }
        }
        sort($checks);
        $this->assertEquals(
            array("a", "call1", "call2", "call3", "d", "g"),
            $checks
        );
    }

    public function testSelectorAttrEquals()
    {
        $q = $this->tree->query("Identifier[name='xxx']");
        $this->assertEquals(1, count($q));
        $this->assertEquals("xxx", $q->get(0)->getName());
    }

    public function testSelectorMultiAttrEquals()
    {
        $q = $this->tree->query("[id.name='call1']");
        $this->assertEquals(1, count($q));
        $this->assertEquals("FunctionDeclaration", $q->get(0)->getType());
    }

    public function testSelectorAttrEqualsCaseInsensitive()
    {
        $q = $this->tree->query("[id.name='CALL1' i]");
        $this->assertEquals(1, count($q));
        $this->assertEquals("FunctionDeclaration", $q->get(0)->getType());
        $this->assertEquals("call1", $q->get(0)->getId()->getName());
    }

    public function testSelectorMultiAttrExists()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name]");
        $this->assertEquals(3, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getId()->getName();
        }
        $this->assertEquals(
            array("call1", "call2", "call3"),
            $checks
        );
    }

    public function testSelectorMultiAttrBeginsWith()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name^='call']");
        $this->assertEquals(3, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getId()->getName();
        }
        $this->assertEquals(
            array("call1", "call2", "call3"),
            $checks
        );
    }

    public function testSelectorMultiAttrBeginsWithCaseInsensitive()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name^='CALL' i]");
        $this->assertEquals(3, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getId()->getName();
        }
        $this->assertEquals(
            array("call1", "call2", "call3"),
            $checks
        );
    }

    public function testSelectorMultiAttrContains()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name*='all']");
        $this->assertEquals(3, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getId()->getName();
        }
        $this->assertEquals(
            array("call1", "call2", "call3"),
            $checks
        );
    }

    public function testSelectorMultiAttrContainsCaseInsensitive()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name*='ALL' i]");
        $this->assertEquals(3, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getId()->getName();
        }
        $this->assertEquals(
            array("call1", "call2", "call3"),
            $checks
        );
    }

    public function testSelectorMultiAttrEndsWith()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name$='ll3']");
        $this->assertEquals(1, count($q));
        $this->assertEquals("call3", $q->get(0)->getId()->getName());
    }

    public function testSelectorMultiAttrEndsWithCaseInsensitive()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name$='LL3' i]");
        $this->assertEquals(1, count($q));
        $this->assertEquals("call3", $q->get(0)->getId()->getName());
    }

    public function testSelectorRegex()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name=/call\d+/]");
        $this->assertEquals(3, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getId()->getName();
        }
        $this->assertEquals(
            array("call1", "call2", "call3"),
            $checks
        );
    }

    public function testSelectorRegexCaseInsensitive()
    {
        $q = $this->tree->query("FunctionDeclaration[id.name=/call\d+/i]");
        $this->assertEquals(3, count($q));
        $checks = array();
        foreach ($q as $node) {
            $checks[] = $node->getId()->getName();
        }
        $this->assertEquals(
            array("call1", "call2", "call3"),
            $checks
        );
    }

    public function testSelectorAttrEqualsEscaped()
    {
        $q = $this->tree->query("Literal[value='That\'s a string']");
        $this->assertEquals(1, count($q));
        $this->assertEquals("That's a string", $q->get(0)->getValue());
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