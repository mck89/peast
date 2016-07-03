<?php
/**
 * This file is part of the REBuilder package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES6\Node;

class ArrowFunctionExpression extends Node implements Expression, Function_
{
    use Extension\Function_;
    
    protected $expression = false;
    
    public function setBody($body)
    {
        $this->assertType($body, array("BlockStatement", "Expression"));
        $this->body = $body;
        return $this;
    }
    
    public function getExpression()
    {
        return $this->expression;
    }
    
    public function setExpression($expression)
    {
        $this->expression = (bool) $expression;
        return $this;
    }
}