<?php
namespace Peast\test\Syntax\Node;

use Peast\Syntax\Node;
use Peast\test\TestBase;

class NullLiteralTest extends TestBase
{
    public function testValue()
    {
        $node = new Node\NullLiteral;
        
        $this->assertEquals(null, $node->getValue());
        $this->assertEquals("null", $node->getRaw());
        
        $node->setValue(123);
        $this->assertEquals(null, $node->getValue());
        $this->assertEquals("null", $node->getRaw());
    }
}