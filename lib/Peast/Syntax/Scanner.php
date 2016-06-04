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
        
        $this->skipWhitespacesAndComments(); //TODO
        
        if ($this->isEOF()) {
            //When the end of the source is reached, check if there are no
            //open brackets
            foreach ($this->openBrackets as $bracket => $num) {
                if ($num) {
                    return $this->error("Unclosed $bracket");
                }
            }
            return null;
        }
        
        //Try to match a token
        $startPosition = $this->getPosition();
        if (($token = $this->scanString()) || //TODO
            ($token = $this->scanTemplate()) || //TODO
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
}