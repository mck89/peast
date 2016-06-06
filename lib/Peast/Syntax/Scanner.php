<?php
namespace Peast\Syntax;

abstract class Scanner
{
    protected $column = 0;
    
    protected $line = 1;
    
    protected $index = 0;
    
    protected $length;
    
    protected $source;
    
    protected $currentToken;
    
    protected $lastToken;
    
    protected $idStartRegex;
    
    protected $idPartRegex;
    
    protected $keywords = array();
    
    protected $punctutators = array();
    
    protected $punctutatorsMap = array();
    
    protected $brackets = array(
        "(" => "", "[" => "", "{" => "", "(" => ")", "[" => "]", "{" => "}"
    );
    
    protected $openBrackets = array();
    
    protected $openTemplates = array();
    
    protected $whitespaces = array(
        " ", "\t", "\n", "\r", "\f", "\v", 0x00A0, 0xFEFF, 0x00A0,
        0x1680, 0x2000, 0x2001, 0x2002, 0x2003, 0x2004, 0x2005, 0x2006,
        0x2007, 0x2008, 0x2009, 0x200A, 0x202F, 0x205F, 0x3000, 0x2028,
        0x2029
    );
    
    protected $lineTerminators = array("\r\n", "\n", "\r", 0x2028, 0x2029);
    
    function __construct($source, $encoding = null)
    {
        //If encoding is missing try to detect it
        if (!$encoding) {
            $encoding = mb_detect_encoding($source);
        }
        
        //Convert to UTF8 if needed
        if ($encoding && !preg_match("/UTF-?8/i", $encoding)) {
            $source = mb_convert_encoding($source, "UTF-8", $encoding);
        }
        
        //Instead of using mb_substr for each character, split the source
        //into an array of UTF8 characters for performance reasons
        $this->source = preg_split('/(?<!^)(?!$)/u', $source);
        $this->length = count($this->source);
        
        //Generate a map by grouping punctutars by their length
        foreach ($this->punctutators as $p) {
            $len = strlen($p);
            if (!isset($this->punctutatorsMap[$len])) {
                $this->punctutatorsMap[$len] = array();
            }
            $this->punctutatorsMap[$len][] = $p;
        }
        
        //Convert character codes to UTF8 characters in whitespaces and line
        //terminators
        foreach (array("whitespaces", "lineTerminators") as $key) {
            foreach ($this->$key as $i => $char) {
                if (is_int($char)) {
                    $this->$key[$i] = Utils::unicodeToUtf8($char);
                }
            }
        }
    }
    
    public function getPosition()
    {
        return new Position(
            $this->getLine(),
            $this->getColumn(),
            $this->getIndex()
        );
    }
    
    public function charAt($index = null)
    {
        if ($index === null) {
            $this->index;
        }
        return $this->isEOF($index) ? null : $this->source[$this->index];
    }
    
    public function isEOF($index = null)
    {
        if ($index === null) {
            $this->index;
        }
        return $index >= $this->length;
    }
    
    protected function error($message = null)
    {
        if (!$message) {
            $message = "Unexpectd " . $this->charAt();
        }
        throw new Exception($message, $this->getPosition());
    }
    
    public function consumeToken(Token $token)
    {
        $this->lastToken = $token;
        $this->currentToken = null;
        return $this;
    }
    
    public function consume($expected)
    {
        $token = $this->getToken();
        if ($token->getSource() === $expected) {
            $this->consumeToken($token);
            return $token;
        }
        return null;
    }
    
    public function getToken()
    {
        //The current token is returned until consumed
        if ($this->currentToken) {
            return $this->currentToken;
        }
        
        $this->skipWhitespacesAndComments();
        
        if ($this->isEOF()) {
            //When the end of the source is reached
            //Check if there are open brackets
            foreach ($this->openBrackets as $bracket => $num) {
                if ($num) {
                    return $this->error("Unclosed $bracket");
                }
            }
            //Check if there are open templates
            if (count($this->openTemplates)) {
                return $this->error("Unterminated template");
            }
            return null;
        }
        
        //Try to match a token
        $startPosition = $this->getPosition();
        if (($token = $this->scanString()) ||
            ($token = $this->scanTemplate()) ||
            ($token = $this->scanNumber()) || //TODO
            ($token = $this->scanRegexp()) || //TODO
            ($token = $this->scanPunctutator()) ||
            ($token = $this->scanKeywordOrIdentifier())) {
            $this->currentToken = $token->setStartPosition($startPosition)
                                        ->setEndPosition($this->getPosition());
            return $this->currentToken;
        }
        
        //No valid token found, error
        return $this->error();
    }
    
    protected function skipWhitespacesAndComments()
    {
        $buffer = "";
        $comment = 0;
        while ($char = $this->charAt()) {
            $buffer .= $char;
            $this->index++;
            $nextChar = $this->charAt();
            if (in_array($char, $this->whitespaces)) {
                //Whitespace
                $buffer .= $char;
                //Exit the comment mode if it is in single line comment mode
                if ($comment === 1 && in_array($char, $this->lineTerminators)) {
                    $comment = 0;
                }
            } elseif (!$comment && $char === "/" &&
                      ($nextChar === "/" || $nextChar === "*")) {
                //Start the comment
                $this->index++;
                $buffer .= $char . $nextChar;
                $comment = $nextChar === "*" ? 2 : 1;
            } elseif ($comment === 2 && $char === "*" && $nextChar === "/") {
                //Exit the comment mode if it is in multiline comment mode and
                //the sequence "*/" is found
                $this->index++;
                $buffer .= $char . $nextChar;
                $comment = 0;
            } elseif ($comment) {
                //Consume every character in comment mode
                $buffer .= $char;
            } else {
                break;
            }
        }
        
        //Error if multiline comment is not terminated
        if ($comment === 2) {
            return $this->error("Unterminated comment");
        }
        
        $this->adjustColumnAndLine($buffer);
    }
    
    protected function scanString()
    {
        $char = $this->charAt();
        if ($char === "'" || $char === '"') {
            $this->index++;
            $stops = array_merge($this->lineTerminators, $char);
            $buffer = $this->consumeUntil($stops);
            if ($buffer === null || $buffer[1] !== $char) {
                return $this->error("Unterminated string");
            }
            $this->adjustColumnAndLine($char . $buffer[0], false);
            return new Token(Token::TYPE_STRING_LITERAL, $char . $buffer[0]);
        }
        
        return null;
    }
    
    protected function scanTemplate()
    {
        $char = $this->charAt();
        
        //Get the current number of open curly brackets
        $openCurly = isset($this->openBrackets["{"]) ? $this->openBrackets["{"] : 0;
        
        //If the character is a curly bracket check and the number of open
        //curly brackets matches the last number in the open templates stack,
        //then the bracket closes the open template expression
        $endExpression = false;
        if ($char === "}") {
            $len = count($this->openTemplates);
            if ($len && $this->openTemplates[$len - 1] === $openCurly) {
                $endExpression = true;
                array_pop($this->openTemplates);
            }
        }
        
        if ($char === "`" || $endExpression) {
            $this->index++;
            $buffer = $char;
            while (true) {
                $tempBuffer = $this->consumeUntil(array("`", "$"));
                if (!$buffer) {
                    return $this->error("Unterminated template");
                }
                $buffer .= $tempBuffer[0];
                if ($tempBuffer[1] !== "$" || $this->charAt() === "{") {
                    //If "${" is found it's a new template expression, register
                    //the current number of open curly brackets in the open
                    //templates stack
                    if ($tempBuffer[1] === "$") {
                        $this->index++;
                        $buffer .= "}";
                        $this->openTemplates[] = $openCurly;
                    }
                    break;
                }
            }
            $this->adjustColumnAndLine($buffer);
            return new Token(Token::TYPE_PUNCTUTATOR, $buffer);
        }
        
        return null;
    }
    
    protected function scanPunctutator()
    {
        $buffer = "";
        $consumed = 0;
        $bestMatch = null;
        
        //This loop scans next characters to find the longest punctutator, so
        //that if "!" is found and it's followed by "=", the matched
        //punctutator will be "!="
        while ($char = $this->charAt($this->index + $consumed)) {
            $buffer .= $char;
            $consumed++;
            //Special handling for brackets
            if (isset($this->brackets[$char])) {
                if ($this->brackets[$char]) {
                    $openBracket = $this->brackets[$char];
                    //Check if there is a corresponding open bracket
                    if (!isset($this->openBrackets[$openBracket]) ||
                        !$this->openBrackets[$openBracket]) {
                        return $this->error();
                    }
                    $this->openBrackets[$openBracket]--;
                } else {
                    if (!isset($this->openBrackets[$char])) {
                        $this->openBrackets[$char] = 0;
                    }
                    $this->openBrackets[$char]++;
                }
                $bestMatch = array($consumed, $buffer);
                break;
            } elseif (in_array($buffer, $this->punctutatorsMap[$consumed])) {
                $bestMatch = array($consumed, $buffer);
            }
            if (!isset($this->punctutatorsMap[$consumed + 1])) {
                break;
            }
        }
        
        if ($bestMatch !== null) {
            $this->index += $bestMatch[0];
            $this->column += $bestMatch[0];
            return new Token(Token::TYPE_PUNCTUTATOR, $bestMatch[1]);
        }
        
        return null;
    }
    
    protected function scanKeywordOrIdentifier()
    {
        //If the first character is not a valid identifier start, exit
        //immediately
        $char = $this->charAt();
        if (!$this->isIdentifierStart($char)) {
            return null;
        }
        
        //Scan next characters that are valid identifier parts
        $buffer = "";
        do {
            $buffer .= $char;
            $this->index++;
            $this->column++;
            $char = $this->charAt();
        } while ($char && $this->isIdentifierPart($char));
        
        //Identify token type
        if ($buffer === "null") {
            $type = Token::TYPE_NULL_LITERAL;
        } elseif ($buffer === "true" || $buffer === "false") {
            $type = Token::TYPE_BOOLEAN_LITERAL;
        } elseif (in_array($buffer, $this->keywords)) {
            $type = Token::TYPE_KEYWORD;
        } else {
            $type = Token::TYPE_IDENTIFIER;
        }
        
        return new Token($type, $buffer);
    }
    
    protected function isIdentifierStart($char)
    {
        return ($char >= "a" && $char <= "z") ||
               ($char >= "A" && $char <= "Z") ||
               $char === "_" || $char === "$" ||
               preg_match($this->idStartRegex, $char);
    }
    
    protected function isIdentifierPart($char)
    {
        return ($char >= "a" && $char <= "z") ||
               ($char >= "A" && $char <= "Z") ||
               ($char >= "0" && $char <= "9") ||
               $char === "_" || $char === "$" ||
               preg_match($this->idPartRegex, $char);
    }
    
    protected function adjustColumnAndLine($buffer, $multiline = true)
    {
        if ($multiline) {
            $regex = "/" . implode("|", $this->lineTerminators) . "/u";
            $lines = preg_split($regex, $buffer);
            $linesCount = count($lines) - 1;
            $this->lines += $linesCount;
        } else {
            $linesCount = 0;
            $lines = array($buffer);
        }
        $columns = mb_strlen($lines[$linesCount], "UTF-8");
        if ($linesCount) {
            $this->column = $columns;
        } else {
            $this->column += $columns;
        }
    }
    
    protected function consumeUntil($stops)
    {
        $buffer = "";
        $escaped = false;
        while ($char = $this->charAt()) {
            $this->index++;
            if (!$escaped && in_array($char, $stops)) {
                $buffer .= $char;
                $this->adjustColumnAndLine($buffer);
                return array($buffer, $char);
            } elseif (!$escaped && $char === "\\") {
                $escaped = true;
            } else {
                $escaped = false;
                $buffer .= $char;
            }
        }
        return null;
    }
}