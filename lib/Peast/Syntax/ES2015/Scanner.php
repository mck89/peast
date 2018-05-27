<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2015;

/**
 * ES2015 scanner.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Scanner extends \Peast\Syntax\Scanner
{
    use \Peast\Syntax\JSX\Scanner;
    
    /**
     * Regex to match identifiers starts
     * 
     * @var string 
     */
    protected $idStartRegex = "/[\p{Lu}\p{Ll}\p{Lt}\p{Lm}\p{Lo}\p{Nl}\x{2118}\x{212E}\x{309B}\x{309C}]/u";
    
    /**
     * Regex to match identifiers parts
     * 
     * @var string 
     */
    protected $idPartRegex = "/[\p{Lu}\p{Ll}\p{Lt}\p{Lm}\p{Lo}\p{Nl}\x{2118}\x{212E}\x{309B}\x{309C}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{00B7}\x{0387}\x{1369}\x{136A}\x{136B}\x{136C}\x{136D}\x{136E}\x{136F}\x{1370}\x{1371}\x{19DA}\x{200C}\x{200D}]/u";
    
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
        "delete", "import", "try", "enum", "await"
    );
    
    /**
     * Array of words that are keywords only in strict mode
     * 
     * @var array 
     */
    protected $strictModeKeywords = array(
        "implements", "interface", "package", "private", "protected", "public",
        "static", "let", "yield"
    );
    
    /**
     * Punctutators array
     * 
     * @var array 
     */
    protected $punctutators = array(
        ".", ";", ",", "<", ">", "<=", ">=", "==", "!=", "===", "!==", "+",
        "-", "*", "%", "++", "--", "<<", ">>", ">>>", "&", "|", "^", "!", "~",
        "&&", "||", "?", ":", "=", "+=", "-=", "*=", "%=", "<<=", ">>=", ">>>=",
        "&=", "|=", "^=", "=>", "...", "/", "/="
    );
    
    /**
     * Octal numbers
     * 
     * @var array 
     */
    protected $onumbers = array("0", "1", "2", "3", "4", "5", "6", "7");
    
    /**
     * Binary numbers
     * 
     * @var array 
     */
    protected $bnumbers = array("0", "1");
}