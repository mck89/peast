<?php
namespace Peast\test\Syntax\Node;

use Peast\Syntax\Node;
use Peast\test\TestBase;

class IdentifierTest extends TestBase
{
    public function testValue()
    {
        $node = new Node\Identifier;
        
        $node->setName('test');
        $this->assertEquals('test', $node->getName());
        $this->assertEquals('test', $node->getRawName());
    }
    
    public function testRaw()
    {
        $node = new Node\Identifier;
        
        $node->setRawName('\u0061\u{73}ync');
        $this->assertEquals('async', $node->getName());
        $this->assertEquals('\u0061\u{73}ync', $node->getRawName());
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