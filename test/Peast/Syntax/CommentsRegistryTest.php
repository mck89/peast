<?php
namespace Peast\test\Syntax;

use \Peast\Syntax\Node\Comment;
use \Peast\Syntax\Token;

class CommentsRegistryTest extends \Peast\test\TestBase
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
                    ),
                    "tokens" => array(
                        array(
                            "endColumn" => 23,
                            "endIndex" => 23,
                            "endLine" => 1,
                            "index" => 0,
                            "startColumn" => 0,
                            "startIndex" => 0,
                            "startLine" => 1,
                            "value" => "<!-- HTML comment start"
                        ),
                        array(
                            "endColumn" => 5,
                            "endIndex" => 29,
                            "endLine" => 2,
                            "index" => 1,
                            "startColumn" => 0,
                            "startIndex" => 24,
                            "startLine" => 2,
                            "value" => "//Doc"
                        ),
                        array(
                            "endColumn" => 9,
                            "endIndex" => 39,
                            "endLine" => 3,
                            "index" => 3,
                            "startColumn" => 4,
                            "startIndex" => 34,
                            "startLine" => 3,
                            "value" => "/*1*/"
                        ),
                        array(
                            "endColumn" => 19,
                            "endIndex" => 49,
                            "endLine" => 3,
                            "index" => 6,
                            "startColumn" => 14,
                            "startIndex" => 44,
                            "startLine" => 3,
                            "value" => "/*2*/"
                        ),
                        array(
                            "endColumn" => 25,
                            "endIndex" => 55,
                            "endLine" => 3,
                            "index" => 8,
                            "startColumn" => 20,
                            "startIndex" => 50,
                            "startLine" => 3,
                            "value" => "/*3*/"
                        ),
                        array(
                            "endColumn" => 33,
                            "endIndex" => 63,
                            "endLine" => 3,
                            "index" => 10,
                            "startColumn" => 28,
                            "startIndex" => 58,
                            "startLine" => 3,
                            "value" => "/*4*/"
                        ),
                        array(
                            "endColumn" => 39,
                            "endIndex" => 69,
                            "endLine" => 3,
                            "index" => 12,
                            "startColumn" => 34,
                            "startIndex" => 64,
                            "startLine" => 3,
                            "value" => "/*5*/"
                        ),
                        array(
                            "endColumn" => 46,
                            "endIndex" => 76,
                            "endLine" => 3,
                            "index" => 14,
                            "startColumn" => 41,
                            "startIndex" => 71,
                            "startLine" => 3,
                            "value" => "/*6*/"
                        ),
                        array(
                            "endColumn" => 53,
                            "endIndex" => 83,
                            "endLine" => 3,
                            "index" => 16,
                            "startColumn" => 48,
                            "startIndex" => 78,
                            "startLine" => 3,
                            "value" => "/*7*/"
                        ),
                        array(
                            "endColumn" => 60,
                            "endIndex" => 90,
                            "endLine" => 3,
                            "index" => 18,
                            "startColumn" => 55,
                            "startIndex" => 85,
                            "startLine" => 3,
                            "value" => "/*8*/"
                        ),
                        array(
                            "endColumn" => 9,
                            "endIndex" => 101,
                            "endLine" => 4,
                            "index" => 20,
                            "startColumn" => 4,
                            "startIndex" => 96,
                            "startLine" => 4,
                            "value" => "/*9*/"
                        ),
                        array(
                            "endColumn" => 21,
                            "endIndex" => 113,
                            "endLine" => 4,
                            "index" => 23,
                            "startColumn" => 15,
                            "startIndex" => 107,
                            "startLine" => 4,
                            "value" => "/*10*/"
                        ),
                        array(
                            "endColumn" => 28,
                            "endIndex" => 120,
                            "endLine" => 4,
                            "index" => 25,
                            "startColumn" => 22,
                            "startIndex" => 114,
                            "startLine" => 4,
                            "value" => "/*11*/"
                        ),
                        array(
                            "endColumn" => 36,
                            "endIndex" => 128,
                            "endLine" => 4,
                            "index" => 28,
                            "startColumn" => 30,
                            "startIndex" => 122,
                            "startLine" => 4,
                            "value" => "/*12*/"
                        ),
                        array(
                            "endColumn" => 6,
                            "endIndex" => 137,
                            "endLine" => 6,
                            "index" => 30,
                            "startColumn" => 0,
                            "startIndex" => 131,
                            "startLine" => 6,
                            "value" => "/*13*/"
                        ),
                        array(
                            "endColumn" => 20,
                            "endIndex" => 158,
                            "endLine" => 7,
                            "index" => 31,
                            "startColumn" => 0,
                            "startIndex" => 138,
                            "startLine" => 7,
                            "value" => "--> HTML comment end"
                        )
                    )
                )
            ),
            array(
                array(
                    "source" => implode("\n", array(
                        "//Start",
                        "/*Before*/[a, b, c] = [1, 2, 3]/*After*/",
                        "//End",
                    )),
                    "nodes" => array(
                        array(
                            "index" => 0,
                            "node" => "ArrayPattern",
                            "leading" => true,
                            "kind" => Comment::KIND_INLINE,
                            "text" => "Start",
                            "rawText" => "//Start"
                        ),
                        array(
                            "index" => 1,
                            "node" => "ArrayPattern",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "Before",
                            "rawText" => "/*Before*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "ArrayExpression",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "After",
                            "rawText" => "/*After*/"
                        ),
                        array(
                            "index" => 1,
                            "node" => "ArrayExpression",
                            "leading" => false,
                            "kind" => Comment::KIND_INLINE,
                            "text" => "End",
                            "rawText" => "//End"
                        ),
                    ),
                    "tokens" => array(
                        array(
                            "endColumn" => 7,
                            "endIndex" => 7,
                            "endLine" => 1,
                            "index" => 0,
                            "startColumn" => 0,
                            "startIndex" => 0,
                            "startLine" => 1,
                            "value" => "//Start"
                        ),
                        array(
                            "endColumn" => 10,
                            "endIndex" => 18,
                            "endLine" => 2,
                            "index" => 1,
                            "startColumn" => 0,
                            "startIndex" => 8,
                            "startLine" => 2,
                            "value" => "/*Before*/"
                        ),
                        array(
                            "endColumn" => 40,
                            "endIndex" => 48,
                            "endLine" => 2,
                            "index" => 17,
                            "startColumn" => 31,
                            "startIndex" => 39,
                            "startLine" => 2,
                            "value" => "/*After*/"
                        ),
                        array(
                            "endColumn" => 5,
                            "endIndex" => 54,
                            "endLine" => 3,
                            "index" => 18,
                            "startColumn" => 0,
                            "startIndex" => 49,
                            "startLine" => 3,
                            "value" => "//End"
                        ),
                    )
                )
            ),
            array(
                array(
                    "source" => implode("\n", array(
                        "/*1*/a = /*2*//\/*3*\/[/*4*/]//*5*/",
                    )),
                    "nodes" => array(
                        array(
                            "index" => 0,
                            "node" => "Identifier",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "1",
                            "rawText" => "/*1*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "RegExpLiteral",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "2",
                            "rawText" => "/*2*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "RegExpLiteral",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "5",
                            "rawText" => "/*5*/"
                        ),
                    ),
                    "tokens" => array(
                        array(
                            "endColumn" => 5,
                            "endIndex" => 5,
                            "endLine" => 1,
                            "index" => 0,
                            "startColumn" => 0,
                            "startIndex" => 0,
                            "startLine" => 1,
                            "value" => "/*1*/"
                        ),
                        array(
                            "endColumn" => 14,
                            "endIndex" => 14,
                            "endLine" => 1,
                            "index" => 3,
                            "startColumn" => 9,
                            "startIndex" => 9,
                            "startLine" => 1,
                            "value" => "/*2*/"
                        ),
                        array(
                            "endColumn" => 35,
                            "endIndex" => 35,
                            "endLine" => 1,
                            "index" => 5,
                            "startColumn" => 30,
                            "startIndex" => 30,
                            "startLine" => 1,
                            "value" => "/*5*/"
                        ),
                    )
                )
            ),
            array(
                array(
                    "source" => implode("\n", array(
                        "/*1*/(a)/*2*/;",
                        "/*3*/(a)/*4*/;",
                        "/*5*/(a) => a/*6*/;",
                    )),
                    "nodes" => array(
                        array(
                            "index" => 0,
                            "node" => "ParenthesizedExpression",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "1",
                            "rawText" => "/*1*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "ParenthesizedExpression",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "2",
                            "rawText" => "/*2*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "ParenthesizedExpression",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "3",
                            "rawText" => "/*3*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "ParenthesizedExpression",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "4",
                            "rawText" => "/*4*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "ArrowFunctionExpression",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "5",
                            "rawText" => "/*5*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "Identifier",
                            "leading" => false,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "6",
                            "rawText" => "/*6*/"
                        ),
                    ),
                    "tokens" => array(
                        array(
                            "endColumn" => 5,
                            "endIndex" => 5,
                            "endLine" => 1,
                            "index" => 0,
                            "startColumn" => 0,
                            "startIndex" => 0,
                            "startLine" => 1,
                            "value" => "/*1*/"
                        ),
                        array(
                            "endColumn" => 13,
                            "endIndex" => 13,
                            "endLine" => 1,
                            "index" => 4,
                            "startColumn" => 8,
                            "startIndex" => 8,
                            "startLine" => 1,
                            "value" => "/*2*/"
                        ),
                        array(
                            "endColumn" => 5,
                            "endIndex" => 20,
                            "endLine" => 2,
                            "index" => 6,
                            "startColumn" => 0,
                            "startIndex" => 15,
                            "startLine" => 2,
                            "value" => "/*3*/"
                        ),
                        array(
                            "endColumn" => 13,
                            "endIndex" => 28,
                            "endLine" => 2,
                            "index" => 10,
                            "startColumn" => 8,
                            "startIndex" => 23,
                            "startLine" => 2,
                            "value" => "/*4*/"
                        ),
                        array(
                            "endColumn" => 5,
                            "endIndex" => 35,
                            "endLine" => 3,
                            "index" => 12,
                            "startColumn" => 0,
                            "startIndex" => 30,
                            "startLine" => 3,
                            "value" => "/*5*/"
                        ),
                        array(
                            "endColumn" => 18,
                            "endIndex" => 48,
                            "endLine" => 3,
                            "index" => 18,
                            "startColumn" => 13,
                            "startIndex" => 43,
                            "startLine" => 3,
                            "value" => "/*6*/"
                        ),
                    )
                )
            ),
            array(
                array(
                    "source" => implode("\n", array(
                        "1+1",
                        "<!--end"
                    )),
                    "nodes" => array(
                        array(
                            "index" => 0,
                            "node" => "Literal",
                            "leading" => false,
                            "kind" => Comment::KIND_HTML_OPEN,
                            "text" => "end",
                            "rawText" => "<!--end"
                        )
                    ),
                    "tokens" => array(
                        array(
                            "endColumn" => 7,
                            "endIndex" => 11,
                            "endLine" => 2,
                            "index" => 3,
                            "startColumn" => 0,
                            "startIndex" => 4,
                            "startLine" => 2,
                            "value" => "<!--end"
                        )
                    )
                )
            ),
            array(
                array(
                    "source" => implode("\n", array(
                        "/*Only*/ //Comments",
                    )),
                    "nodes" => array(
                        array(
                            "index" => 0,
                            "node" => "Program",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "Only",
                            "rawText" => "/*Only*/"
                        ),
                        array(
                            "index" => 1,
                            "node" => "Program",
                            "leading" => true,
                            "kind" => Comment::KIND_INLINE,
                            "text" => "Comments",
                            "rawText" => "//Comments"
                        )
                    ),
                    "tokens" => array(
                        array(
                            "endColumn" => 8,
                            "endIndex" => 8,
                            "endLine" => 1,
                            "index" => 0,
                            "startColumn" => 0,
                            "startIndex" => 0,
                            "startLine" => 1,
                            "value" => "/*Only*/"
                        ),
                        array(
                            "endColumn" => 19,
                            "endIndex" => 19,
                            "endLine" => 1,
                            "index" => 1,
                            "startColumn" => 9,
                            "startIndex" => 9,
                            "startLine" => 1,
                            "value" => "//Comments"
                        )
                    )
                )
            ),
            //
            array(
                array(
                    "source" => implode("\n", array(
                        "var a = /*Start*/<element></element>//End",
                    )),
                    "nodes" => array(
                        array(
                            "index" => 0,
                            "node" => "JSXOpeningElement",
                            "leading" => true,
                            "kind" => Comment::KIND_MULTILINE,
                            "text" => "Start",
                            "rawText" => "/*Start*/"
                        ),
                        array(
                            "index" => 0,
                            "node" => "JSXClosingElement",
                            "leading" => false,
                            "kind" => Comment::KIND_INLINE,
                            "text" => "End",
                            "rawText" => "//End"
                        )
                    ),
                    "tokens" => array(
                        array(
                            "endColumn" => 17,
                            "endIndex" => 17,
                            "endLine" => 1,
                            "index" => 3,
                            "startColumn" => 8,
                            "startIndex" => 8,
                            "startLine" => 1,
                            "value" => "/*Start*/"
                        ),
                        array(
                            "endColumn" => 41,
                            "endIndex" => 41,
                            "endLine" => 1,
                            "index" => 11,
                            "startColumn" => 36,
                            "startIndex" => 36,
                            "startLine" => 1,
                            "value" => "//End"
                        )
                    ),
                    "options" => array(
                        "jsx" => true
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
        $options = array("comments" => true);
        if (isset($data["options"])) {
            $options = array_merge($options, $data["options"]);
        }
        \Peast\Peast::latest($data["source"], $options)->parse()->traverse(function ($node) use (&$comments) {
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
    
    /**
     * @dataProvider dataProvider
     */
    public function testTokenize($data)
    {
        $comments = array();
        $options = array("comments" => true);
        if (isset($data["options"])) {
            $options = array_merge($options, $data["options"]);
        }
        $tokens = \Peast\Peast::latest($data["source"], $options)->tokenize();
        foreach ($tokens as $idx => $token) {
            if ($token->getType() === Token::TYPE_COMMENT) {
                $loc = $token->getLocation();
                $start = $loc->getStart();
                $end = $loc->getEnd();
                $comments[] = array(
                    "index" => $idx,
                    "value" => $token->getValue(),
                    "startLine" => $start->getLine(),
                    "startColumn" => $start->getColumn(),
                    "startIndex" => $start->getIndex(),
                    "endLine" => $end->getLine(),
                    "endColumn" => $end->getColumn(),
                    "endIndex" => $end->getIndex()
                );
            }
        }
        $this->assertEquals(count($data["tokens"]), count($comments));
        foreach ($data["tokens"] as $k => $token) {
            $this->assertEquals($token, $comments[$k]);
        }
    }
}