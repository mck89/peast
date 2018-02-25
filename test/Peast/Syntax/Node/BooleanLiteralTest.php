<?php
namespace Peast\test\Syntax\Node;

use \Peast\Syntax\Node;

class BooleanLiteralTest extends \Peast\test\TestBase
{
    public function testValue()
    {
        $node = new Node\BooleanLiteral;
        
        $node->setValue(true);
        $this->assertEquals(true, $node->getValue());
        $this->assertEquals("true", $node->getRaw());
        
        $node->setValue(false);
        $this->assertEquals(false, $node->getValue());
        $this->assertEquals("false", $node->getRaw());
        
        $node->setValue("true");
        $this->assertEquals(true, $node->getValue());
        $this->assertEquals("true", $node->getRaw());
        
        $node->setValue("false");
        $this->assertEquals(false, $node->getValue());
        $this->assertEquals("false", $node->getRaw());
        
        $node->setValue(1);
        $this->assertEquals(true, $node->getValue());
        $this->assertEquals("true", $node->getRaw());
        
        $node->setValue(0);
        $this->assertEquals(false, $node->getValue());
        $this->assertEquals("false", $node->getRaw());
    }
}