<?php
namespace Peast\test\Syntax\ES2015;

use Peast\Syntax\Utils;

class ES2015Test extends \Peast\test\TestParser
{
    protected $parser = "ES2015";
    
    protected function getTestVersions()
    {
        return array("ES2015");
    }
    
    public function testParseEmptySource()
    {
        $tree = \Peast\Peast::{$this->parser}("")->parse();
        $this->assertTrue($tree->getType() === "Program");
        $this->assertSame(0, count($tree->getBody()));
    }
    
    public function testTokenizeEmptySource()
    {
        $tree = \Peast\Peast::{$this->parser}("")->tokenize();
        $this->assertSame(0, count($tree));
    }
    
    public function keywordIdentifierProvider()
    {
        return array(
            array("try{}catch(yield){}", true, false),
            array("while(true){continue yield}", true, false),
            array("while(true){break yield}", true, false),
            array("function yield(){}", true, false),
            array("class yield{}", false, false),
            array("var yield", true, false),
            array("let yield", true, false),
            array("export {interface as yield}", true, true),
            array("import yield from 'source'", false, false),
            array("[a, ...yield] = b", true, false),
            array("var a = {yield:1, if:2, true:3}", true, true),
            array("a.yield.true.if", true, true),
            array("yield.abc", true, false),
            array("var true", false, false),
            array("for (let in {}) { }", true, false),
            array("for (let of {}) { }", false, false),
            array("let = 2", true, false),
            array("const = 2", false, false),
            array("import {if as a} from 'source'", true, true),
            array("import {if} from 'source'", false, false),
            array("import * as yield from 'source'", false, false),
            array("export {a as if};", true, true),
            array("function *test(){var yield;}", false, false)
        );
    }
    
    /**
     * @dataProvider keywordIdentifierProvider
     */
    public function testKeywordIdentifier($code, $valid, $validStrictMode)
    {
        $options = array(
            "sourceType" => preg_match("#import|export#", $code) ?
                            \Peast\Peast::SOURCE_TYPE_MODULE :
                            \Peast\Peast::SOURCE_TYPE_SCRIPT
        );
        foreach (array($valid, $validStrictMode) as $strict => $isValid) {
            $exCode = ($strict ? '"use strict";' : '') . $code;
            $validResult = true;
            try {
                \Peast\Peast::{$this->parser}($exCode, $options)->parse();
            } catch (\Exception $ex) {
                $validResult = false;
            }
            $this->assertSame($isValid, $validResult);
        }
    }
    
    public function stringCharsProvider()
    {
        return array(
            array("\\x", false),
            array("\\x1", false),
            array("\\x1G", false),
            array("\\u", false),
            array("\\u1", false),
            array("\\u11", false),
            array("\\u111", false),
            array("\\uG", false),
            array("\\u1G", false),
            array("\\u11G", false),
            array("\\u111G", false),
            array("\\u{}", false),
            array("\\u{", false),
            array("\\u{12", false),
            array("\\u{G}", false),
            array("\\u{1G}", false),
            array("\\u{1G1}", false),
            array("\\u{G1}", false),
            array("\\u{{", false),
            array("\n", false),
            array("\r", false),
            array(\Peast\Syntax\Utils::unicodeToUtf8(0x2028), false),
            array(\Peast\Syntax\Utils::unicodeToUtf8(0x2029), false),
            array("\\\n", true),
            array("\\\r", true),
            array("\\\r\n", true),
            array("\\" . \Peast\Syntax\Utils::unicodeToUtf8(0x2028), true),
            array("\\" . \Peast\Syntax\Utils::unicodeToUtf8(0x2029), true)
        );
    }
    
    /**
     * @dataProvider stringCharsProvider
     */
    public function testStringsParsing($chars, $valid)
    {
        $code = "'$chars'";
        $validResult = true;
        try {
            \Peast\Peast::{$this->parser}($code)->parse();
        } catch (\Exception $ex) {
            $validResult = false;
        }
        $this->assertSame($valid, $validResult);
    }

    public function surrogatePairsProvider()
    {
        $tests = array();
        foreach (array('"', "'", "`") as $char) {
            for ($i = 0; $i <= 1; $i++) {
                for ($c = 0; $c <= 1; $c++) {
                    $tests[] = array($char, $i, $c);
                }
            }
        }
        return $tests;
    }    

    /**
     * @dataProvider surrogatePairsProvider
     */
    public function testSurrogatePairs($char, $firstBraces, $secondBraces)
    {
        $test = $char;
        foreach (array("D83D" => $firstBraces, "DE00" => $secondBraces) as $point => $braces) {
            $test .= '\u' . ($braces ? "{" : "") . $point . ($braces ? "}" : "");
        }
        $test .= $char;
        $check = Utils::unicodeToUtf8(hexdec("1F600"));
        
        $body = \Peast\Peast::{$this->parser}($test)->parse()->getBody();
        if ($char === "`") {
            $testVal = $body[0]->getExpression()->getQuasis()[0]->getValue();
        } else {
            $testVal = $body[0]->getExpression()->getValue();
        }

        $this->assertSame($testVal, $check);
    }
}