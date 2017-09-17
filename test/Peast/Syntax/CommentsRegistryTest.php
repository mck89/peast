<?php
namespace test\Peast\Syntax;

use \Peast\Syntax\Node\Comment;
use \Peast\Syntax\Token;

class CommentsRegistryTest extends \test\Peast\TestBase
{
    public function dataProvider()
    {
        return array(
            array(
                array(
                    "source" => implode("\n", array(
                        "<!-- HTML comment start",
                        "//Doc",
                        "for /*1*/(var /*2*/i/*3*/ = /*4*/0/*5*/; /*6*/; /*7*/) /*8*/{",
                        "    /*9*/alert(/*10*/i/*11*/);/*12*/",
                        "}",
                        "/*13*/",
                        "--> HTML comment end",
                    )),
                    "nodes" => array(
                        array(
                            "index" => 0,
                            "node" => "ForStatement",
                            "leading" => true,
                            "kind" => Comment::KIND_HTML_OPEN,
                            "text" => " HTML comment start",
                            "rawText" => "<!-- HTML comment start"
                        ),
                        array(
                            "index" => 1,
                            "node" => "ForStatement",
                            "leading" => true,
                            "kind" => Comment::KIND_INLINE,
                            "text" => "Doc",
                            "rawText" => "//Doc"
                        ),
                        array(
                            "index" => 2,
                            "node" => "ForStatement",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "1",
                            "rawText" => "/*1*/"
                        ),
                        array(
                            "index" => 3,
                            "node" => "ForStatement",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "6",
                            "rawText" => "/*6*/"
                        ),
                        array(
                            "index" => 4,
                            "node" => "ForStatement",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "7",
                            "rawText" => "/*7*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "Identifier",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "2",
                            "rawText" => "/*2*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "Identifier",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "3",
                            "rawText" => "/*3*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "Literal",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "4",
                            "rawText" => "/*4*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "Literal",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "5",
                            "rawText" => "/*5*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "BlockStatement",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "8",
                            "rawText" => "/*8*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "BlockStatement",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "13",
                            "rawText" => "/*13*/"
                        ),
                        array(
                            "index" => 1,
                            "node" => "BlockStatement",
                            "leading" => false,
                            "kind" => Comment::KIND_HTML_CLOSE,
                            "text" => " HTML comment end",
                            "rawText" => "--> HTML comment end"
                        ),
                        array(
                            "index" => 0,
                            "node" => "ExpressionStatement",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "12",
                            "rawText" => "/*12*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "Identifier",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "9",
                            "rawText" => "/*9*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "Identifier",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "10",
                            "rawText" => "/*10*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "Identifier",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "11",
                            "rawText" => "/*11*/"
                        ),
                    )
                )
            )
        );
    }
    
    /**
     * @dataProvider dataProvider
     */
    public function testParse($data)
    {
        $comments = array();
        \Peast\Peast::latest($data["source"], array("comments" => true))->parse()->traverse(function ($node) use (&$comments) {
            foreach(array("getLeadingComments", "getTrailingComments") as $k => $fn) {
                $nodeComments = $node->$fn();
                if ($nodeComments) {
                    foreach ($nodeComments as $idx => $comment) {
                        $comments[] = array(
                            "index" => $idx,
                            "node" => $node->getType(),
                            "leading" => $k === 0,
                            "kind" => $comment->getKind(),
                            "text" => $comment->getText(),
                            "rawText" => $comment->getRawText()
                        );
                    }
                }
            }
        });
        $this->assertEquals(count($data["nodes"]), count($comments));
        foreach ($data["nodes"] as $node) {
            $testComment = null;
            foreach ($comments as $comment) {
                if ($node["text"] === $comment["text"]) {
                    $testComment = $comment;
                    break;
                }
            }
            $this->assertEquals($node, $testComment);
        }
    }
}