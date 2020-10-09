<?php
namespace Peast\test\Syntax\Node;

use Peast\Syntax\Node;
use Peast\test\TestBase;

class StringLiteralTest extends TestBase
{
    public function testValue()
    {
        $node = new Node\StringLiteral;
        
        $node->setValue('abc"def');
        $this->assertEquals('abc"def', $node->getValue());
        $this->assertEquals('"abc\\"def"', $node->getRaw());
        $this->assertEquals($node::DOUBLE_QUOTED, $node->getFormat());
        
        $node->setFormat($node::SINGLE_QUOTED);
        $this->assertEquals('abc"def', $node->getValue());
        $this->assertEquals("'abc\"def'", $node->getRaw());
        $this->assertEquals($node::SINGLE_QUOTED, $node->getFormat());
    }
    
    public function testRaw()
    {
        $node = new Node\StringLiteral;
        
        $node->setRaw("'abc\\x20'");
        $this->assertEquals("abc ", $node->getValue());
        $this->assertEquals("'abc\\x20'", $node->getRaw());
        $this->assertEquals($node::SINGLE_QUOTED, $node->getFormat());
    }
    
    public function invalidStringsProvider()
    {
        return array(
            array("abc"),
            array("'abc"),
            array("abc'"),
            array("\"abc'"),
            array(""),
            array(array()),
        );
    }
    
    /**
     * @dataProvider invalidStringsProvider
     */
    public function testInvalidString($string)
    {
        $this->expectException('Exception');

        $node = new Node\StringLiteral;
        $node->setRaw($string);
    }
}