<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES7;

/**
 * ES7 scanner.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Scanner extends \Peast\Syntax\Scanner\ES6\Scanner
{
    /**
     * Punctutators array
     * 
     * @var array 
     */
    protected $punctutators = array(
        ".", ";", ",", "<", ">", "<=", ">=", "==", "!=", "===", "!==", "+",
        "-", "*", "%", "++", "--", "<<", ">>", ">>>", "&", "|", "^", "!", "~",
        "&&", "||", "?", ":", "=", "+=", "-=", "*=", "%=", "<<=", ">>=", ">>>=",
        "&=", "|=", "^=", "=>", "...", "/", "/=", "**", "**="
    );
}