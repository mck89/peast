<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2016;

/**
 * ES2016 parser class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Parser extends \Peast\Syntax\ES2015\Parser
{
    /**
     * Assignment operators
     * 
     * @var array 
     */
    protected $assignmentOperators = array(
        "=", "+=", "-=", "*=", "/=", "%=", "<<=", ">>=", ">>>=", "&=", "^=",
        "|=", "**="
    );
    
    /**
     * Logical and binary operators
     * 
     * @var array 
     */
    protected $logicalBinaryOperators = array(
        "||" => 0,
        "&&" => 1,
        "|" => 2,
        "^" => 3,
        "&" => 4,
        "===" => 5, "!==" => 5, "==" => 5, "!=" => 5,
        "<=" => 6, ">=" => 6, "<" => 6, ">" => 6,
        "instanceof" => 6, "in" => 6,
        ">>>" => 7, "<<" => 7, ">>" => 7,
        "+" => 8, "-" => 8,
        "*" => 9, "/" => 9, "%" => 9,
        "**" => 10
    );
}