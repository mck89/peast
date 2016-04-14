<?php
namespace Peast\Syntax\ES6;

class Config extends Peast\Syntax\Config
{
    static protected $instance;
    
    protected $reservedWords = array(
        "break", "do", "in", "typeof", "case", "else", "instanceof", "var",
        "catch", "export", "new", "void", "class", "extends", "return", "while",
        "const", "finally", "super", "with", "continue", "for", "switch",
        "yield", "debugger", "function", "this", "default", "if", "throw",
        "delete", "import", "try", "enum", "implements", "package", "protected",
        "interface", "private", "public", "null", "true", "false"
    );
    
    protected $symbols = array(
        "{", "}", "(", ")", "[", "]", ".", ";", ",", "<", ">", "<=",
        ">=", "==", "!=", "===", "!==", "+", "-", "*", "%", "++", "--",
        "<<", ">>", ">>>", "&", "|", "^", "!", "~", "&&", "||", "?",
        ":", "=", "+=", "-=", "*=", "%=", "<<=", ">>=", ">>>=", "&=",
        "|=", "^=", "=>", "...", "/*", "*/", "//", '"', "'", "`", '${',
        "/", "/="
    );
    
    protected $whitespaces = array(
        " ", "\t", "\n", "\r", 0x000B, 0x000C, 0x00A0, 0xFEFF, 0x00A0,
        0x1680, 0x2000, 0x2001, 0x2002, 0x2003, 0x2004, 0x2005, 0x2006,
        0x2007, 0x2008, 0x2009, 0x200A, 0x202F, 0x205F, 0x3000, 0x2028,
        0x2029
    );
    
    protected $lineTerminators = array("\n", "\r", 0x2028, 0x2029);
    
    static public function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getReservedWords($moduleMode = false)
    {
        $words = $this->reservedWords;
        if ($moduleMode) {
            $words[] = "await";
        }
        return $words;
    }
    
    public function getIdRegex($part = false)
    {
        $regex = "\p{Lu}\p{Ll}\p{Lt}\p{Lm}\p{Lo}\p{Nl}".
                 "\x{2118}\x{212E}\x{309B}\x{309C}";
        if ($part) {
            $regex .= "\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{00B7}\x{0387}\x{1369}" .
                      "\x{136A}\x{136B}\x{136C}\x{136D}\x{136E}\x{136F}" .
                      "\x{1370}\x{1371}\x{19DA}\x{200C}\x{200D}";
        }
        return "/$regex/u";
    }
    
    public function getSymbols()
    {
        return $this->symbols;
    }
    
    public function getWhitespaces()
    {
        return $this->cachedCompiledUnicodeArray("whitespaces");
    }
    
    public function getLineTerminators()
    {
        return $this->cachedCompiledUnicodeArray("lineTerminators");
    }
    
    public function getLineTerminatorsSequences()
    {
        return array_merge(array("\r\n"), $this->getLineTerminators());
    }
}