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

/**
 * A node that represents a function expression
 * For example: var test = function () {}
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class FunctionExpression extends Node implements Expression, Function_
{
    use Extension\Function_;
}