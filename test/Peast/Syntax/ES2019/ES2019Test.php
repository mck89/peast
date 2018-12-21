<?php
namespace Peast\test\Syntax\ES2019;

class ES2019Test extends \Peast\test\Syntax\ES2018\ES2018Test
{
    protected $parser = "ES2019";
    
    protected function getTestVersions()
    {
        return array("ES2015", "ES2016", "ES2017", "ES2018", "ES2019");
    }
    
    protected function getExcludedTests()
    {
        $excluded = parent::getExcludedTests();
        return array_merge(
            $excluded,
            array(
                "TryStatement/InvalidCatch2.js"
            )
        );
    }
    
    public function stringCharsProvider()
    {
        $chars = parent::stringCharsProvider();
        $validChars = array(
            \Peast\Syntax\Utils::unicodeToUtf8(0x2028),
            \Peast\Syntax\Utils::unicodeToUtf8(0x2029)
        );
        foreach ($chars as $k => $v) {
            if (in_array($v[0], $validChars)) {
                $chars[$k][1] = true;
            }
        }
        return $chars;
    }
}