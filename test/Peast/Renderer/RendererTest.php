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
        
        list($ppTest, $cmTest) = preg_split(
            "#\s+/\*{50}/\s+#",
            file_get_contents($compareFile)
        );
        
        $this->assertEquals($ppTest, $pp);
        $this->assertEquals($cmTest, $cm);
    }
}