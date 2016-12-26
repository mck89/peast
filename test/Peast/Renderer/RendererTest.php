<?php
namespace test\Peast\Traverser;

class RendererTest extends \test\Peast\TestBase
{
    protected function getTestVersions()
    {
        return array("ES6", "ES7");
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
        $options = array(
            "sourceType" => strpos($sourceFile, "modules") !== false ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT
        );
        $source = file_get_contents($sourceFile);
        $tree = \Peast\Peast::ES7($source, $options)->parse();
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
    
    /**
     * @expectedException \Exception
     */
    public function testExceptionOnMissingFormatter()
    {
        $tree = \Peast\Peast::ES7("")->parse();
        $renderer = new \Peast\Renderer;
        $this->assertEquals(null, $renderer->getFormatter());
        $renderer->render($tree);
    }
    
    public function testSemicolonAfterLabelledStatement()
    {
        $source = "label:var test;";
        $tree = \Peast\Peast::ES7($source)->parse();
        $res = $tree->render(new \Peast\Formatter\Compact);
        $this->assertEquals($source, $res);
    }
}