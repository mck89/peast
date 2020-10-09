<?php
namespace Peast\test\Syntax\Node;

use Peast\Syntax\Node;
use Peast\test\TestBase;

class CommentTest extends TestBase
{
    public function testAddComments()
    {
        $node = new Node\Comment;
        $node2 = new Node\Comment;
        
        $node->setLeadingComments(array($node2));
        $node->setTrailingComments(array($node2));
        
        $this->assertEquals(0, count($node->getLeadingComments()));
        $this->assertEquals(0, count($node->getTrailingComments()));
    }

    public function testInvalidRawText()
    {
        $this->expectException('Exception');

        $node = new Node\Comment;
        $node->setRawText("test");
    }
    
    public function testJsonConversion()
    {
        $node = new Node\Comment;
        $node->setRawText("/*test*/");
        
        $json = json_decode(json_encode($node));
        
        $this->assertTrue(!isset($json->leadingComments));
        $this->assertTrue(!isset($json->trailingComments));
    }
}