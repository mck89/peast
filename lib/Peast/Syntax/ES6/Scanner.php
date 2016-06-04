<?php
namespace Peast\Syntax\ES6;

class Scanner extends \Peast\Syntax\Scanner
{
    protected $idStartRegex = "/[".
                                "\p{Lu}\p{Ll}\p{Lt}\p{Lm}\p{Lo}\p{Nl}" .
                                "\x{2118}\x{212E}\x{309B}\x{309C}" .
                              "/u";
    
    protected $idPartRegex = "/[" .
                                "\p{Lu}\p{Ll}\p{Lt}\p{Lm}\p{Lo}\p{Nl}" .
                                "\x{2118}\x{212E}\x{309B}\x{309C}" .
                                "\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{00B7}\x{0387}" .
                                "\x{1369}\x{136A}\x{136B}\x{136C}\x{136D}" .
                                "\x{136E}\x{136F}\x{1370}\x{1371}\x{19DA}" .
                                "\x{200C}\x{200D}" .
                             "/u";
    
    protected $keywords = array(
        "break", "do", "in", "typeof", "case", "else", "instanceof", "var",
        "catch", "export", "new", "void", "class", "extends", "return", "while",
        "const", "finally", "super", "with", "continue", "for", "switch",
        "yield", "debugger", "function", "this", "default", "if", "throw",
        "delete", "import", "try", "enum", "implements", "package", "protected",
        "interface", "private", "public", "await"
    );
    
    protected $punctutators = array(
        ".", ";", ",", "<", ">", "<=", ">=", "==", "!=", "===", "!==", "+",
        "-", "*", "%", "++", "--", "<<", ">>", ">>>", "&", "|", "^", "!", "~",
        "&&", "||", "?", ":", "=", "+=", "-=", "*=", "%=", "<<=", ">>=", ">>>=",
        "&=", "|=", "^=", "=>", "...", "/*", "*/", "//", "/", "/="
    );
}