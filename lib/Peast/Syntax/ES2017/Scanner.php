<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2017;

/**
 * ES2017 scanner.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Scanner extends \Peast\Syntax\ES2016\Scanner
{
    /**
     * Keywords array
     * 
     * @var array 
     */
    protected $keywords = array(
        "break", "do", "in", "typeof", "case", "else", "instanceof", "var",
        "catch", "export", "new", "void", "class", "extends", "return", "while",
        "const", "finally", "super", "with", "continue", "for", "switch",
        "debugger", "function", "this", "default", "if", "throw",
        "delete", "import", "try", "enum"
    );
}