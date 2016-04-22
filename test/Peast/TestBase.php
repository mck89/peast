<?php
namespace test\Peast;

class TestBase extends \PHPUnit_Framework_TestCase
{
    protected function getJsTestFiles($dir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $testFiles = array();
        foreach (glob($dir . $ds . "files" . $ds . "*.js") as $jsFile) {
            $testFiles[] = array($jsFile, str_replace(".js", ".json", $jsFile));
        }
        return $testFiles;
    }
    
    protected function compareJSFile($tree, $compareFile)
    {
        $compareTree = json_decode(file_get_contents($compareFile));
        $this->objectTestRecursive($tree, $compareTree);
    }
    
    protected function objectTestRecursive($obj, $compare)
    {
        if (gettype($obj) === "object") {
            foreach ($compare as $k => $v) {
                $fn = "get" . ucfirst($k);
                $this->objectTestRecursive($v, $obj->$fn());
            }
        } elseif ($compare === null) {
            $this->assertSame($compare, $obj);
        }
    }
}