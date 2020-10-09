<?php
namespace Peast\test\Syntax;

use Peast\test\TestBase;

class ScannerTest extends TestBase
{
    public function testSourceEncodingConversion()
    {
        if (function_exists("mb_convert_encoding")) {
            $UTF8Char = chr(0xc2) . chr(0xb0);
            $encoding = "UTF-16BE";
            $source = mb_convert_encoding("'$UTF8Char'", $encoding, "UTF-8");
            $options = array("sourceEncoding" => $encoding);
            $tree = \Peast\Peast::latest($source, $options)->parse();
            $str = $tree->getBody()[0]->getExpression()->getValue();
            $this->assertEquals($UTF8Char, $str);
        }
    }

    public function testExceptionOnInvalidUTF8()
    {
        $this->expectException('Peast\Syntax\EncodingException');

        $UTF8Char = chr(0xc2) . chr(0xb0);
        $source = "'" . $UTF8Char . $UTF8Char[0] . "'";
        \Peast\Peast::latest($source)->parse();
    }
    
    public function testHandleInvalidUTF8UsingStrictEncodingOpt()
    {
        if (function_exists("mb_convert_encoding")) {
            $UTF8Char = chr(0xc2) . chr(0xb0);
            $source = "'" . $UTF8Char . $UTF8Char[0] . "'";
            $options = array("strictEncoding" => false);
            $tree = \Peast\Peast::latest($source, $options)->parse();
            $str = $tree->getBody()[0]->getExpression()->getValue();
            $this->assertTrue(strpos($str, $UTF8Char) !== false);
        }
    }
    
    public function BOMProvider()
    {
        return array(
            array("UTF-8", "\xEF\xBB\xBF"),
            array("UTF-16BE", "\xFE\xFF"),
            array("UTF-16LE", "\xFF\xFE")
        );
    }
    
    /**
     * @dataProvider BOMProvider
     */
    public function testHandleBOM($encoding, $bom)
    {
        if (function_exists("mb_convert_encoding")) {
            $UTF8Char = chr(0xc2) . chr(0xb0);
            $source = "'$UTF8Char'";
            if ($encoding !=="UTF-8") {
                $source = mb_convert_encoding($source, $encoding, "UTF-8");
            }
            $source = $bom . $source;
            $tree = \Peast\Peast::latest($source)->parse();
            $str = $tree->getBody()[0]->getExpression()->getValue();
            $this->assertEquals($UTF8Char, $str);
        }
    }
}