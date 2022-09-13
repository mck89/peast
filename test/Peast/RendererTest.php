<?php
namespace Peast\test;

class RendererTest extends TestBase
{
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019", "ES2020", "ES2021", "ES2022", "ES2023");
    }
    
    public function jsParserTestFilesProvider()
    {
        return parent::getJsTestFiles(self::JS_RENDERER);
    }
    
    /**
     * @dataProvider jsParserTestFilesProvider
     */
    public function testRenderer($sourceFile, $compareFile)
    {
        $module = strpos($sourceFile, "modules") !== false;
        $jsx = strpos($sourceFile, "JSX") !== false;
        $options = array(
            "sourceType" => $module ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT,
            "jsx" => $jsx
        );
        $source = file_get_contents($sourceFile);
        $tree = \Peast\Peast::latest($source, $options)->parse();
        $renderer = new \Peast\Renderer;
        $pp = $renderer->setFormatter(new \Peast\Formatter\PrettyPrint)->render($tree);
        $cm = $renderer->setFormatter(new \Peast\Formatter\Compact)->render($tree);
        $ex = $renderer->setFormatter(new \Peast\Formatter\Expanded)->render($tree);
        
        list($ppTest, $cmTest, $exTest) = preg_split(
            "#\s+/\*{50}/\s+#",
            file_get_contents($compareFile)
        );
        
        $this->assertEquals($ppTest, $pp);
        $this->assertEquals($cmTest, $cm);
        $this->assertEquals($exTest, $ex);
    }

    public function testExceptionOnMissingFormatter()
    {
        $this->expectException('Exception');

        $tree = \Peast\Peast::latest("")->parse();
        $renderer = new \Peast\Renderer;
        $this->assertEquals(null, $renderer->getFormatter());
        $renderer->render($tree);
    }
    
    public function testSemicolonAfterLabelledStatement()
    {
        $source = "label:var test;";
        $tree = \Peast\Peast::latest($source)->parse();
        $res = $tree->render(new \Peast\Formatter\Compact);
        $this->assertEquals($source, $res);
    }

    public function dontTrustComputedOnMemberExpressionsProvider()
    {
        return array(
            array("a[b]", "a.b"),
            array("a[1]", "a[1]"),
            array("a['b']", "a['b']"),
            array("a[b+c]", "a[b+c]")
        );
    }

    /**
     * @dataProvider dontTrustComputedOnMemberExpressionsProvider
     */
    public function testDontTrustComputedOnMemberExpressions($source, $result)
    {
        $tree = \Peast\Peast::latest($source)->parse();
        $tree->getBody()[0]->getExpression()->setComputed(false);
        $res = $tree->render(new \Peast\Formatter\Compact);
        $this->assertEquals($result . ";", $res);
    }

    public function commentsRenderingProvider()
    {
        return array(
            array(
                array(
                    "//test",
                    "/*test*/"
                ),
                array(
                    "//test",
                    "/*test*/"
                ),
            ),
            array(
                array(
                    "function test () {",
                    "//test",
                    "/*test*/",
                    "}"
                ),
                array(
                    "function test () {",
                    "//test",
                    "/*test*/",
                    "}"
                ),
            ),
            array(
                array(
                    "function test () {",
                    "/*1*/",
                    "for /*2*/(/*3*/var/*4*/i/*5*/=/*6*/0;/*7*/i/*8*/</*9*/10/*10*/;/*11*/i++/*12*/) {/*13*/",
                    "/*14*/",
                    "test(/*15*/i)/*16*/;/*17*/",
                    "/*18*/",
                    "}",
                    "/*19*/",
                    "}",
                    "/*20*/"
                ),
                array(
                    "function test () {",
                    "/*1*/",
                    "/*2*/",
                    "for (/*3*/var /*4*/i/*5*/ = /*6*/0; /*7*/i/*8*/ < /*9*/10/*10*/; /*11*/i++/*12*/) {",
                    "/*13*/",
                    "/*14*/",
                    "test(/*15*/i)/*16*/;/*17*/",
                    "/*18*/",
                    "/*19*/",
                    "}",
                    "}",
                    "/*20*/"
                )
            ),
            array(
                array(
                    "//comment",
                    "var test = 1;",
                    "switch(test) {",
                    "//case 1",
                    "case 1:",
                    "call(1);",
                    "break;",
                    "//case 2",
                    "case 2:",
                    "call(2);",
                    "break;",
                    "//case default",
                    "default:",
                    "/*This is called with null*/",
                    "call(null);",
                    "break;",
                    "}"
                ),
                array(
                    "//comment",
                    "var test = 1;",
                    "switch (test) {",
                    "//case 1",
                    "case 1:",
                    "call(1);",
                    "break;",
                    "//case 2",
                    "case 2:",
                    "call(2);",
                    "break;",
                    "//case default",
                    "default:",
                    "/*This is called with null*/",
                    "call(null);",
                    "break;",
                    "}"
                )
            ),
            array(
                array(
                    "var arr = [",
                    "1/*one*/,",
                    "2/*two*/,",
                    "3/*three*/",
                    "];",
                ),
                array(
                    "var arr = [1/*one*/, 2/*two*/, 3/*three*/];"
                )
            ),
            array(
                array(
                    "/*",
                    "* Class",
                    "*/",
                    "class test {",
                    "/*",
                    "* Class method",
                    "*/",
                    "method () {",
                    "//Method body",
                    "}",
                    "}"
                    ),
                array(
                    "/*",
                    "* Class",
                    "*/",
                    "class test {",
                    "/*",
                    "* Class method",
                    "*/",
                    "method () {",
                    "//Method body",
                    "}",
                    "}"
                )
            ),
            array(
                array(
                    "<!-- start",
                    "var test = 1;",
                    "--> end",
                ),
                array(
                    "<!-- start",
                    "var test = 1;",
                    "--> end",
                )
            ),
            array(
                array(
                    "#!hashbang",
                    "var test = 1;"
                ),
                array(
                    "#!hashbang",
                    "var test = 1;"
                )
            ),
            array(
                array(
                    "#!hashbang"
                ),
                array(
                    "#!hashbang"
                )
            )
        );
    }

    /**
     * @dataProvider commentsRenderingProvider
     */
    public function testCommentsRenderingProvider($source, $result)
    {
        $source = implode("\n", $source);
        $tree = \Peast\Peast::latest($source, array("comments" => true))->parse();
        $res = $tree->render(new \Peast\Formatter\PrettyPrint(true));
        $compare = array();
        foreach (explode("\n", trim($res)) as $line) {
            $compare[] = trim($line);
        }
        $this->assertEquals($result, $compare);
    }
}