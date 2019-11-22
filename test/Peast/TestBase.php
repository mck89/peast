<?php
namespace Peast\test;

abstract class TestBase extends TestCaseBase
{
    const JS_INVALID = 1;
    
    const JS_PARSE = 2;
    
    const JS_TOKENIZE = 3;
    
    const JS_RENDERER = 4;
    
    protected function getTestVersions() {}
    
    protected function getExcludedTests() {return array();}
    
    protected function getJsTestFiles($jsType = self::JS_PARSE)
    {
        $invalid = $jsType === self::JS_INVALID;
        $ds = DIRECTORY_SEPARATOR;
        $testFiles = array();
        $files = array();
        $dir = __DIR__;
        foreach ($this->getTestVersions() as $version) {
            $files = array_merge(
                $files,
                glob($dir . $ds . "Syntax" . $ds . $version . $ds . "files" . $ds . "*" . $ds . "*.js"),
                glob($dir . $ds . "Syntax" . $ds . $version . $ds . "files" . $ds . "*" . $ds . "*" . $ds . "*.js")
            );
        }
        $excludedTests = array_flip($this->getExcludedTests());
        foreach ($files as $jsFile) {
            $isInvalid = strpos($jsFile, "Invalid");
            $parts = explode($ds, $jsFile);
            $testName = implode("/", array_slice($parts, -2));
            if (isset($excludedTests[$testName])) {
                continue;
            }
            if ($isInvalid && $invalid) {
                $testFiles[$testName] = array($jsFile);
            } elseif (!$isInvalid && !$invalid) {
                if ($jsType === self::JS_TOKENIZE) {
                    $replacement = ".Tokens.json";
                    $op = "Tokenize";
                } elseif ($jsType === self::JS_RENDERER) {
                    $replacement = ".Render.txt";
                    $op = "Render";
                } else {
                    $replacement = ".json";
                    $op = "Parse";
                }
                $testFiles["$op $testName"] = array(
                    $jsFile,
                    str_replace(".js", $replacement, $jsFile)
                );
            }
        }
        return $testFiles;
    }
}