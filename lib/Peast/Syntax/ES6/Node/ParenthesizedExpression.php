<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES6\Node;

class ParenthesizedExpression extends Node implements Expression
{
    protected $expression;
    
    public function getExpression()
    {
        return $this->expression;
    }
    
    public function setExpression(Expression $expression)
    {
        $this->expression = $expression;
        return $this;
    }
}