<?php
namespace Peast\test\Traverser;

class RendererTest extends \Peast\test\TestBase
{
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019");
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
    
    /**
     * @expectedException \Exception
     */
    public function testExceptionOnMissingFormatter()
    {
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
}