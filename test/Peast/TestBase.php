<?php
namespace test\Peast;

class TestBase extends \PHPUnit_Framework_TestCase
{
    protected $ignoredKeys = array(
        "TryStatement" => array("guardedHandlers", "handlers")
    );
    
    protected function getJsTestFiles($dir, $invalid = false)
    {
        $ds = DIRECTORY_SEPARATOR;
        $testFiles = array();
        $files = glob($dir . $ds . "files" . $ds . "*" . $ds . "*.js");
        foreach ($files as $jsFile) {
            $isInvalid = strpos($jsFile, "Invalid");
            $parts = explode($ds, $jsFile);
            $testName = implode($ds, array_slice($parts, -2));
            if ($isInvalid && $invalid) {
                $testFiles[$testName] = array($jsFile);
            } elseif (!$isInvalid && !$invalid) {
                $testFiles[$testName] = array(
                    $jsFile,
                    str_replace(".js", ".json", $jsFile)
                );
            }
        }
        return $testFiles;
    }
    
    protected function compareJSFile($tree, $compareFile)
    {
        $compareTree = json_decode(file_get_contents($compareFile));
        $this->objectTestRecursive($compareTree, $tree);
    }
    
    protected function objectTestRecursive($compare, $obj, $message = "")
    {
        $objType = gettype($obj);
        $this->assertSame(gettype($compare), $objType, "gettype($message)");
        switch ($objType)
        {
            case "object":
                $ignored = isset($compare->type) && isset($this->ignoredKeys[$compare->type]) ?
                           $this->ignoredKeys[$compare->type] :
                           array();
                foreach ($compare as $k => $v) {
                    if (in_array($k, $ignored)) {
                        continue;
                    } elseif ($k === "loc") {
                        $objValue = $obj->getLocation();
                    } elseif ($k === "range") {
                        $loc = $obj->getLocation();
                        $objValue = array(
                            $loc->getStart()->getIndex(),
                            $loc->getEnd()->getIndex()
                        );
                    } else {
                        $fn = "get" . ucfirst($k);
                        $objValue = $obj->$fn();
                    }
                    $this->objectTestRecursive($v, $objValue, "$message" . "->$k");
                }
            break;
            case "array":
                $this->assertSame(count($compare), count($obj), "count($message)");
                foreach ($compare as $k => $v) {
                    $this->objectTestRecursive($v, $obj[$k], "$message" . "[$k]");
                }
            break;
            default:
                $this->assertSame($compare, $obj, $message);
            break;
        }
    }
}