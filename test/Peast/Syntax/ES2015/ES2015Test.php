<?php
namespace Peast\test\Syntax\ES2015;

class ES2015Test extends \Peast\test\TestBase
{
    protected $parser = "ES2015";
    
    protected function getTestVersions()
    {
        return array("ES2015");
    }
    
    public function jsParserTestFilesProvider()
    {
        return parent::getJsTestFiles();
    }
    
    /**
     * @dataProvider jsParserTestFilesProvider
     */
    public function testParser($sourceFile, $compareFile)
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
        $tree = \Peast\Peast::{$this->parser}($source, $options)->parse();
        $this->compareJSFile($tree, $compareFile);
    }
    
    public function jsTokenizerTestFilesProvider()
    {
        return parent::getJsTestFiles(self::JS_TOKENIZE);
    }
    
    /**
     * @dataProvider jsTokenizerTestFilesProvider
     */
    public function testTokenizer($sourceFile, $compareFile)
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
        $tree = \Peast\Peast::{$this->parser}($source, $options)->tokenize();
        $this->compareJSFile($tree, $compareFile, true);
    }
    
    public function invalidJsTestFilesProvider()
    {
        return parent::getJsTestFiles(self::JS_INVALID);
    }
    
    /**
     * @expectedException \Peast\Syntax\Exception
     * @dataProvider invalidJsTestFilesProvider
     */
    public function testParserException($sourceFile)
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
        \Peast\Peast::{$this->parser}($source, $options)->parse();
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
    
    public function escapeSequencesProvider()
    {
        return array(
            array("'\\x'"),
            array("'\\x1'"),
            array("'\\x1G'"),
            array("'\\u'"),
            array("'\\u1'"),
            array("'\\u11'"),
            array("'\\u111'"),
            array("'\\uG'"),
            array("'\\u1G'"),
            array("'\\u11G'"),
            array("'\\u111G'"),
            array("'\\u{}'"),
            array("'\\u{'"),
            array("'\\u{12'"),
            array("'\\u{G}'"),
            array("'\\u{1G}'"),
            array("'\\u{1G1}'"),
            array("'\\u{G1}'"),
            array("'\\u{{'"),
        );
    }
    
    /**
     * @dataProvider escapeSequencesProvider
     * @expectedException \Peast\Syntax\Exception
     */
    public function testInvalidescapeSequences($code)
    {
        \Peast\Peast::{$this->parser}($code)->parse();
    }
    
    public function validStringsProvider()
    {
        return array(
            array("\\\n"), //LF
            array("\\\r"), //CR
            array("\\\r\n"), //CR+LF
            array(\Peast\Syntax\Utils::unicodeToUtf8(0x2028)), //LineSeparator
            array(\Peast\Syntax\Utils::unicodeToUtf8(0x2029)), //ParagraphSeparator
        );
    }
    
    /**
     * @dataProvider validStringsProvider
     */
    public function testValidStrings($code)
    {
        $code = "'$code'";
        $tree = \Peast\Peast::{$this->parser}($code)->parse();
        $items = $tree->getBody();
        $str = $items[0]->getExpression()->getRaw();
        $this->assertSame($code, $str);
    }
}