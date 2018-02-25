<?php
namespace Peast\test\Syntax\Node;

use \Peast\Syntax\Node;

class NumericLiteralTest extends \Peast\test\TestBase
{
    public function testValue()
    {
        $node = new Node\NumericLiteral;
        
        $node->setValue(123);
        $this->assertEquals(123, $node->getValue());
        $this->assertEquals('123', $node->getRaw());
        $this->assertEquals(Node\NumericLiteral::DECIMAL, $node->getFormat());
        
        $node->setValue("123.45");
        $this->assertEquals(123.45, $node->getValue());
        $this->assertEquals('123.45', $node->getRaw());
        $this->assertEquals(Node\NumericLiteral::DECIMAL, $node->getFormat());
    }
    
    public function rawValuesProvider()
    {
        return array(
            array(123, 123, "123", Node\NumericLiteral::DECIMAL),
            array(123.45, 123.45, "123.45", Node\NumericLiteral::DECIMAL),
            array("123.0", 123, "123.0", Node\NumericLiteral::DECIMAL),
            array(".1", 0.1, ".1", Node\NumericLiteral::DECIMAL),
            array("1.", 1, "1.", Node\NumericLiteral::DECIMAL),
            array("0", 0, "0", Node\NumericLiteral::DECIMAL),
            array("123e2", 12300, "123e2", Node\NumericLiteral::DECIMAL),
            array("456e+3", 456000, "456e+3", Node\NumericLiteral::DECIMAL),
            array("789e-4", 0.0789, "789e-4", Node\NumericLiteral::DECIMAL),
            array("159.45e-2", 1.5945, "159.45e-2", Node\NumericLiteral::DECIMAL),
            array("0x20", 32, "0x20", Node\NumericLiteral::HEXADECIMAL),
            array("0XFFA", 4090, "0XFFA", Node\NumericLiteral::HEXADECIMAL),
            array("0o123", 83, "0o123", Node\NumericLiteral::OCTAL),
            array("0O77", 63, "0O77", Node\NumericLiteral::OCTAL),
            array("0b110011", 51, "0b110011", Node\NumericLiteral::BINARY),
            array("0B00001111", 15, "0B00001111", Node\NumericLiteral::BINARY),
            array("0777", 511, "0777", Node\NumericLiteral::OCTAL),
            array("088", 88, "088", Node\NumericLiteral::DECIMAL),
        );
    }
    
    public function testFormatChange()
    {
        $node = new Node\NumericLiteral;
        
        $node->setValue(1200);
        $this->assertEquals(1200, $node->getValue());
        $this->assertEquals('1200', $node->getRaw());
        $this->assertEquals(Node\NumericLiteral::DECIMAL, $node->getFormat());
        
        $node->setFormat(Node\NumericLiteral::HEXADECIMAL);
        $this->assertEquals(1200, $node->getValue());
        $this->assertEquals('0x4b0', $node->getRaw());
        $this->assertEquals(Node\NumericLiteral::HEXADECIMAL, $node->getFormat());
        
        $node->setFormat(Node\NumericLiteral::OCTAL);
        $this->assertEquals(1200, $node->getValue());
        $this->assertEquals('0o2260', $node->getRaw());
        $this->assertEquals(Node\NumericLiteral::OCTAL, $node->getFormat());
        
        $node->setFormat(Node\NumericLiteral::BINARY);
        $this->assertEquals(1200, $node->getValue());
        $this->assertEquals('0b10010110000', $node->getRaw());
        $this->assertEquals(Node\NumericLiteral::BINARY, $node->getFormat());
    }
    
    /**
     * @dataProvider rawValuesProvider
     */
    public function testRaw($test, $value, $raw, $format)
    {
        $node = new Node\NumericLiteral;
        
        $node->setRaw($test);
        $this->assertEquals($value, $node->getValue());
        $this->assertEquals($raw, $node->getRaw());
        $this->assertEquals($format, $node->getFormat());
    }
    
    public function invalidNumbersProvider()
    {
        return array(
            array(array()),
            array(")"),
            array("."),
            array("e12"),
            array("21.21.21"),
            array("0x"),
            array("0o"),
            array("0b"),
            array("0xZ"),
            array("0o8"),
            array("0b13"),
        );
    }
    
    /**
     * @dataProvider invalidNumbersProvider
     * 
     * @expectedException \Exception
     */
    public function testInvalidNumbers($num)
    {
        $node = new Node\NumericLiteral;
        $node->setRaw($num);
    }
}