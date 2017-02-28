<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax;

/**
 * Base class for scanners.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 * 
 * @abstract
 */
abstract class Scanner
{
    /**
     * Current column
     * 
     * @var int
     */
    protected $column = 0;
    
    /**
     * Current line
     * 
     * @var int
     */
    protected $line = 1;
    
    /**
     * Current index
     * 
     * @var int
     */
    protected $index = 0;
    
    /**
     * Source length
     * 
     * @var int
     */
    protected $length;
    
    /**
     * Source characters
     * 
     * @var array
     */
    protected $source;
    
    /**
     * Consumed position
     * 
     * @var Position
     */
    protected $position;
    
    /**
     * Current token
     * 
     * @var Token 
     */
    protected $currentToken;
    
    /**
     * Next token
     * 
     * @var Token 
     */
    protected $nextToken;
    
    /**
     * Strict mode flag
     * 
     * @var bool 
     */
    protected $strictMode = false;
    
    /**
     * True to register tokens in the tokens array
     * 
     * @var bool 
     */
    protected $registerTokens = false;
    
    /**
     * Module mode
     * 
     * @var bool 
     */
    protected $isModule = false;
    
    /**
     * Registered tokens array
     * 
     * @var array 
     */
    protected $tokens = array();
    
    /**
     * Regex to match identifiers starts
     * 
     * @var string 
     */
    protected $idStartRegex;
    
    /**
     * Regex to match identifiers parts
     * 
     * @var string 
     */
    protected $idPartRegex;
    
    /**
     * Keywords array
     * 
     * @var array 
     */
    protected $keywords = array();
    
    /**
     * Array of words that are keywords only in strict mode
     * 
     * @var array 
     */
    protected $strictModeKeywords = array();
    
    /**
     * Punctutators array
     * 
     * @var array 
     */
    protected $punctutators = array();
    
    /**
     * Punctutators map
     * 
     * @var array 
     */
    protected $punctutatorsMap = array();
    
    /**
     * Brackets array
     * 
     * @var array 
     */
    protected $brackets = array(
        "(" => "", "[" => "", "{" => "", ")" => "(", "]" => "[", "}" => "{"
    );
    
    /**
     * Open brackets array
     * 
     * @var array 
     */
    protected $openBrackets = array();
    
    /**
     * Open templates array
     * 
     * @var array 
     */
    protected $openTemplates = array();
    
    /**
     * Whitespaces array
     * 
     * @var array 
     */
    protected $whitespaces = array(
        " ", "\t", "\n", "\r", "\f", "\v", 0x00A0, 0xFEFF, 0x00A0,
        0x1680, 0x2000, 0x2001, 0x2002, 0x2003, 0x2004, 0x2005, 0x2006,
        0x2007, 0x2008, 0x2009, 0x200A, 0x202F, 0x205F, 0x3000, 0x2028,
        0x2029
    );
    
    /**
     * Line terminators characters array
     * 
     * @var array 
     * 
     * @static
     */
    public static $lineTerminatorsChars = array("\n", "\r", 0x2028, 0x2029);
    
    /**
     * Line terminators sequences array
     * 
     * @var array
     * 
     * @static
     */
    public static $lineTerminatorsSequences = array("\r\n");
    
    /**
     * Regex to split texts using valid ES line terminators
     * 
     * @var array 
     */
    protected $linesSplitter;
    
    /**
     * Concatenation of line terminators characters and line terminators
     * sequences
     * 
     * @var array 
     */
    protected $lineTerminators;
    
    /**
     * Properties to copy when getting the scanner state
     * 
     * @var array
     */
    protected $stateProps = array("position", "index", "column", "line",
                                  "currentToken", "nextToken", "strictMode",
                                  "openBrackets", "openTemplates");
    
    /**
     * Decimal numbers
     * 
     * @var array
     */
    protected $numbers = array("0", "1", "2", "3", "4", "5", "6", "7", "8",
                               "9");
    
    /**
     * Hexadecimal numbers
     * 
     * @var array
     */
    protected $xnumbers = array("0", "1", "2", "3", "4", "5", "6", "7", "8",
                                "9", "a", "b", "c", "d", "e", "f", "A", "B",
                                "C", "D", "E", "F");
    
    /**
     * Class constructor
     * 
     * @param string $source   Source code
     * @param string $encoding Source code encoding, if not specified it
     *                         will assume UTF-8
     * @param bool   $isModule If true the scanner will scan in module mode
     */
    function __construct($source, $encoding = null, $isModule = false)
    {
        $this->isModule = $isModule;
        
        //Convert to UTF8 if needed
        if ($encoding && !preg_match("/UTF-?8/i", $encoding)) {
            $source = mb_convert_encoding($source, "UTF-8", $encoding);
        }
        
        //Instead of using mb_substr for each character, split the source
        //into an array of UTF8 characters for performance reasons
        $this->source = $source === "" ?
                        array() :
                        preg_split('//u', $source, null, PREG_SPLIT_NO_EMPTY);
        $this->length = count($this->source);
        
        //Generate a map by grouping punctutars by their first character
        foreach ($this->punctutators as $p) {
            $first = $p[0];
            $len = strlen($p);
            if (!isset($this->punctutatorsMap[$first])) {
                $this->punctutatorsMap[$first] = array(
                    "maxLen" => 0,
                    "map" => array()
                );
            }
            $this->punctutatorsMap[$first]["map"][] = $p;
            $this->punctutatorsMap[$first]["maxLen"] = max(
                $this->punctutatorsMap[$first]["maxLen"], $len
            );
        }
        
        //Convert character codes to UTF8 characters in whitespaces and line
        //terminators
        $this->lineTerminators = array_merge(
            self::$lineTerminatorsSequences, self::$lineTerminatorsChars
        );
        foreach (array("whitespaces", "lineTerminators") as $key) {
            foreach ($this->$key as $i => $char) {
                if (is_int($char)) {
                    $this->{$key}[$i] = Utils::unicodeToUtf8($char);
                }
            }
        }
        
        $this->linesSplitter = "/" .
                               implode("|", $this->lineTerminators) .
                               "/u";
        $this->position = new Position(0, 0, 0);
    }
    
    /**
     * Enables or disables tokens registration in the token array
     * 
     * @param bool $enable True to enable token registration, false to disable
     *                     it
     * 
     * @return $this
     */
    public function enableTokenRegistration($enable = true)
    {
        $this->registerTokens = $enable;
        return $this;
    }
    
    /**
     * Return registered tokens
     * 
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }
    
    /**
     * Enables or disables strict mode
     * 
     * @param bool $strictMode Strict mode state
     * 
     * @return $this
     */
    public function setStrictMode($strictMode)
    {
        $this->strictMode = $strictMode;
        return $this;
    }
    
    /**
     * Return strict mode state
     * 
     * @return bool
     */
    public function getStrictMode()
    {
        return $this->strictMode;
    }
    
    /**
     * Checks if the given token is a keyword in the current strict mode state
     * 
     * @param Token $token Token to checks
     * 
     * @return bool
     */
    public function isStrictModeKeyword($token)
    {
        return $token->getType() === Token::TYPE_KEYWORD &&
               (in_array($token->getValue(), $this->keywords) || (
                $this->strictMode &&
                in_array($token->getValue(), $this->strictModeKeywords)));
    }
    
    /**
     * Returns the current scanner state
     * 
     * @return array
     */
    public function getState()
    {
        //Consume current and next tokens so that they wont' be parsed again
        //if the state is restored
        $this->getNextToken();
        $state = array();
        foreach ($this->stateProps as $prop) {
            $state[$prop] = $this->$prop;
        }
        if ($this->registerTokens) {
            $state["tokensNum"] = count($this->tokens);
        }
        return $state;
    }
    
    /**
     * Sets the current scanner state
     * 
     * @param array $state State
     * 
     * @return $this
     */
    public function setState($state)
    {
        if ($this->registerTokens) {
            $this->tokens = array_slice($this->tokens, 0, $state["tokensNum"]);
            unset($state["tokensNum"]);
        }
        foreach ($state as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }
    
    /**
     * Returns current scanner state
     * 
     * @param bool $scanPosition By default this method returns the scanner
     *                           consumed position, if this parameter is true
     *                           the scanned position will be returned
     * 
     * @return Position
     */
    public function getPosition($scanPosition = false)
    {
        if ($scanPosition) {
            return new Position($this->line, $this->column, $this->index);
        } else {
            return $this->position;
        }
    }
    
    /**
     * Return the character at the given index in the source code or null if the
     * end is reached.
     * 
     * @param int $index Index, if not given it will use the current index
     * 
     * @return string|null
     */
    public function charAt($index = null)
    {
        if ($index === null) {
            $index = $this->index;
        }
        return $this->isEOF($index) ? null : $this->source[$index];
    }
    
    /**
     * Checks if the given index is at the end of the source code
     * 
     * @param int $index Index, if not given it will use the current index
     * 
     * @return bool
     */
    public function isEOF($index = null)
    {
        if ($index === null) {
            $index = $this->index;
        }
        return $index >= $this->length;
    }
    
    /**
     * Throws a syntax error
     * 
     * @param string $message Error message
     * 
     * @return void
     * 
     * @throws Exception
     */
    protected function error($message = null)
    {
        if (!$message) {
            $message = "Unexpectd " . $this->charAt();
        }
        throw new Exception($message, $this->getPosition(true));
    }
    
    /**
     * Consumes the current token
     * 
     * @return $this
     */
    public function consumeToken()
    {
        //Move the scanner position to the end of the current position
        $this->position = $this->currentToken->getLocation()->getEnd();
        
        //Register the token if required
        if ($this->registerTokens) {
            $this->tokens[] = $this->currentToken;
        }
        
        $this->currentToken = $this->nextToken ? $this->nextToken : null;
        $this->nextToken = null;
        return $this;
    }
    
    /**
     * Checks if the given string is matched, if so it consumes the token
     * 
     * @param string $expected String to check
     * 
     * @return Token|null
     */
    public function consume($expected)
    {
        $token = $this->getToken();
        if ($token && $token->getValue() === $expected) {
            $this->consumeToken();
            return $token;
        }
        return null;
    }
    
    /**
     * Checks if one of the given strings is matched, if so it consumes the
     * token
     * 
     * @param array $expected Strings to check
     * 
     * @return Token|null
     */
    public function consumeOneOf($expected)
    {
        $token = $this->getToken();
        if ($token && in_array($token->getValue(), $expected)) {
            $this->consumeToken();
            return $token;
        }
        return null;
    }
    
    /**
     * Checks that there are not line terminators following the current scan
     * position before next token
     * 
     * @param bool $nextToken By default it checks the current token position
     *                        relative to the current position, if this
     *                        parameter is true the check will be done relative
     *                        to the next token
     * 
     * @return bool
     */
    public function noLineTerminators($nextToken = false)
    {
        if ($nextToken) {
            $nextToken = $this->getNextToken();
            $refLine = !$nextToken ? null :
                        $nextToken->getLocation()->getEnd()->getLine();
        } else {
            $refLine = $this->getPosition()->getLine();
        }
        $token = $this->getToken();
        return $token &&
               $token->getLocation()->getEnd()->getLine() === $refLine;
    }
    
    /**
     * Checks if one of the given strings follows the current scan position
     * 
     * @param string|array $expected  String or array of strings to check
     * @param bool         $nextToken This parameter must be true if the first
     *                                parameter is an array so that it will
     *                                check also next tokens
     * 
     * @return bool
     */
    public function isBefore($expected, $nextToken = false)
    {
        $token = $this->getToken();
        if (!$token) {
            return false;
        } elseif (in_array($token->getValue(), $expected)) {
            return true;
        } elseif (!$nextToken) {
            return false;
        }
        if (!$this->getNextToken()) {
            return false;
        }
        foreach ($expected as $val) {
            if (is_array($val) && $val[0] === $token->getValue() &&
                $val[1] === $this->nextToken->getValue()
            ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns the next token
     * 
     * @return Token|null
     */
    public function getNextToken()
    {
        if (!$this->nextToken) {
            $token = $this->getToken();
            $this->currentToken = null;
            $this->nextToken = $this->getToken();
            $this->currentToken = $token;
        }
        return $this->nextToken;
    }
    
    /**
     * Returns the current token 
     * 
     * @return Token|null
     */
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
        $startPosition = $this->getPosition(true);
        if (($token = $this->scanString()) ||
            ($token = $this->scanTemplate()) ||
            ($token = $this->scanNumber()) ||
            ($token = $this->scanPunctutator()) ||
            ($token = $this->scanKeywordOrIdentifier())
        ) {
            $this->currentToken = $token->setStartPosition($startPosition)
                                        ->setEndPosition($this->getPosition(true));
            return $this->currentToken;
        }
        
        //If last token was "/" do not throw an error if the token has not be
        //recognized since it can be the first character in a regexp and it will
        //be consumed when the current token will be reconsumed as a regexp
        if ($this->isAfterSlash()) {
            return null;
        }
        
        //No valid token found, error
        return $this->error();
    }
    
    /**
     * Checks if the last scanned character is a slash, this method is used
     * to know if the scanner is at the beginning of a regex
     * 
     * @return bool
     */
    protected function isAfterSlash()
    {
        return $this->index && ($char = $this->charAt($this->index - 1)) &&
               $char === "/";
    }
    
    /**
     * Tries to reconsume the current token as a regexp if possible
     * 
     * @return Token|null
     */
    public function reconsumeCurrentTokenAsRegexp()
    {
        $token = $this->getToken();
        $value = $token ? $token->getValue() : null;
        
        //Check if the token starts with "/"
        if (!$value || $value[0] !== "/") {
            return null;
        }
        
        //Reset the scanner position to the token's start position
        $startPosition = $token->getLocation()->getStart();
        $this->index = $startPosition->getIndex();
        $this->column = $startPosition->getColumn();
        $this->line = $startPosition->getLine();
        
        $buffer = "/";
        $this->index++;
        $this->column++;
        $inClass = false;
        while (true) {
            //In a characters class the delmiter "/" is allowed without escape,
            //so the characters class must be closed before closing the regexp
            $stops = $inClass ? array("]") : array("/", "[");
            $tempBuffer = $this->consumeUntil($stops);
            if ($tempBuffer === null) {
                if ($inClass) {
                    return $this->error(
                        "Unterminated character class in regexp"
                    );
                } else {
                    return $this->error("Unterminated regexp");
                }
            }
            $buffer .= $tempBuffer[0];
            if ($tempBuffer[1] === "/") {
                break;
            } else {
                $inClass = $tempBuffer[1] === "[";
            }
        }
        
        //Flags
        while (($char = $this->charAt()) !== null) {
            $lower = strtolower($char);
            if ($lower >= "a" && $lower <= "z") {
                $buffer .= $char;
                $this->index++;
                $this->column++;
            } else {
                break;
            }
        }
        
        //If next token has already been parsed and it's a bracket exclude it
        //from the count of open brackets
        if ($this->nextToken) {
            $nextVal = $this->nextToken->getValue();
            if (isset($this->brackets[$nextVal]) &&
                isset($this->openBrackets[$nextVal])
            ) {
                if ($this->brackets[$nextVal]) {
                    $this->openBrackets[$nextVal]++;
                } else {
                    $this->openBrackets[$nextVal]--;
                }
            }
            $this->nextToken = null;
        }
            
        //Replace the current token with a regexp token
        $token = new Token(Token::TYPE_REGULAR_EXPRESSION, $buffer);
        $this->currentToken = $token->setStartPosition($startPosition)
                                    ->setEndPosition($this->getPosition(true));
        return $this->currentToken;
    }
    
    /**
     * Skips whitespaces and comments from the current scan position
     * 
     * @return void
     */
    protected function skipWhitespacesAndComments()
    {
        $content = "";
        while (($char = $this->charAt()) !== null) {
            //Whitespace
            if (in_array($char, $this->whitespaces)) {
                $content .= $char;
                $this->index++;
            } elseif ($char === "/") {
                //Comment
                $nextChar = $this->charAt($this->index + 1);
                if ($nextChar === "/") {
                    //Inline comment
                    $this->index += 2;
                    $content .= $char . $nextChar;
                    while (($char = $this->charAt()) !== null) {
                        $content .= $char;
                        $this->index++;
                        if (in_array($char, $this->lineTerminators)) {
                            break;
                        }
                    }
                } elseif ($nextChar === "*") {
                    //Multiline comment
                    $this->index += 2;
                    $content .= $char . $nextChar;
                    $closed = false;
                    while (($char = $this->charAt()) !== null) {
                        $content .= $char;
                        $this->index++;
                        if ($char === "*" &&
                            $nextChar = $this->charAt() === "/"
                        ) {
                            $content .= $nextChar;
                            $this->index++;
                            $closed = true;
                            break;
                        }
                    }
                    if (!$closed) {
                        return $this->error("Unterminated comment");
                    }
                } else {
                    break;
                }
            } elseif (!$this->isModule && $char === "<" &&
                $this->charAt($this->index + 1) === "!" &&
                $this->charAt($this->index + 2) === "-" &&
                $this->charAt($this->index + 3) === "-"
            ) {
                //Open html comment
                $this->index += 4;
                $content .= "<!--";
                while (($char = $this->charAt()) !== null) {
                    $content .= $char;
                    $this->index++;
                    if (in_array($char, $this->lineTerminators)) {
                        break;
                    }
                }
            } elseif (!$this->isModule && $char === "-" &&
                $this->charAt($this->index + 1) === "-" &&
                $this->charAt($this->index + 2) === ">"
            ) {
                //Close html comment
                //Check if it is on it's own line
                $allow = true;
                for ($index = $this->index - 1; $index >= 0; $index--) {
                    $char = $this->charAt($index);
                    if (!in_array($char, $this->whitespaces)) {
                        $allow = false;
                        break;
                    } elseif (in_array($char, $this->lineTerminators)) {
                        break;
                    }
                }
                if ($allow) {
                    $this->index += 3;
                    $content .= "-->";
                    while (($char = $this->charAt()) !== null) {
                        $content .= $char;
                        $this->index++;
                        if (in_array($char, $this->lineTerminators)) {
                            break;
                        }
                    }
                } else {
                    break;
                }
            } else {
                break;
            }
        }
        
        if ($content !== "") {
            $this->adjustColumnAndLine($content);
        }
    }
    
    /**
     * String scanning method
     * 
     * @return Token|null
     */
    protected function scanString()
    {
        $char = $this->charAt();
        if ($char === "'" || $char === '"') {
            $this->index++;
            $this->column++;
            $stops = array_merge($this->lineTerminators, array($char));
            $buffer = $this->consumeUntil($stops);
            if ($buffer === null || $buffer[1] !== $char) {
                return $this->error("Unterminated string");
            }
            return new Token(Token::TYPE_STRING_LITERAL, $char . $buffer[0]);
        }
        
        return null;
    }
    
    /**
     * Template scanning method
     * 
     * @return Token|null
     */
    protected function scanTemplate()
    {
        $char = $this->charAt();
        
        //Get the current number of open curly brackets
        $openCurly = isset($this->openBrackets["{"]) ?
                     $this->openBrackets["{"] :
                     0;
        
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
            $this->column++;
            $buffer = $char;
            while (true) {
                $tempBuffer = $this->consumeUntil(array("`", "$"));
                if (!$tempBuffer) {
                    return $this->error("Unterminated template");
                }
                $buffer .= $tempBuffer[0];
                if ($tempBuffer[1] !== "$" || $this->charAt() === "{") {
                    //If "${" is found it's a new template expression, register
                    //the current number of open curly brackets in the open
                    //templates stack
                    if ($tempBuffer[1] === "$") {
                        $this->index++;
                        $this->column++;
                        $buffer .= "{";
                        $this->openTemplates[] = $openCurly;
                    }
                    break;
                }
            }
            return new Token(Token::TYPE_TEMPLATE, $buffer);
        }
        
        return null;
    }
    
    /**
     * Number scanning method
     * 
     * @return Token|null
     */
    protected function scanNumber()
    {
        //Numbers can start with a decimal nuber or with a dot (.5)
        $char = $this->charAt();
        if (!(($char >= "0" && $char <= "9") || $char === ".")) {
            return null;
        }
        
        $buffer = "";
        $allowedExp = true;
        
        //Parse the integer part
        if ($char !== ".") {
            
            //Consume all decimal numbers
            $buffer = $this->consumeNumbers();
            $char = $this->charAt();
            $lower = $char !== null ? strtolower($char) : null;
            
            //Handle hexadecimal (0x), octal (0o) and binary (0b) forms
            if ($buffer === "0" && $lower !== null &&
                isset($this->{$lower . "numbers"})
            ) {
                
                $this->index++;
                $this->column++;
                $tempBuffer = $this->consumeNumbers($lower);
                if ($tempBuffer === null) {
                    return $this->error("Missing numbers after 0$char");
                }
                $buffer .= $char . $tempBuffer;
                
                //Check that there are not numbers left
                if ($this->consumeNumbers() !== null) {
                    return $this->error();
                }
                
                return new Token(Token::TYPE_NUMERIC_LITERAL, $buffer);
            }
            
            //Consume exponent part if present
            if ($tempBuffer = $this->consumeExponentPart()) {
                $buffer .= $tempBuffer;
                $allowedExp = false;
            }
        }
        
        //Parse the decimal part
        if ($this->charAt() === ".") {
            
            //Consume the dot
            $this->index++;
            $this->column++;
            $buffer .= ".";
            
            //Consume all decimal numbers
            $tempBuffer = $this->consumeNumbers();
            $buffer .= $tempBuffer;
            
            //If the buffer contains only the dot it should be parsed as
            //punctutator
            if ($buffer === ".") {
                $this->index--;
                $this->column--;
                return null;
            }
            
            //Consume exponent part if present
            if (($tempBuffer = $this->consumeExponentPart()) !== null) {
                if (!$allowedExp) {
                    return $this->error("Invalid exponential notation");
                }
                $buffer .= $tempBuffer;
            }
        }
        
        return new Token(Token::TYPE_NUMERIC_LITERAL, $buffer);
    }
    
    /**
     * Consumes the maximum number of digits
     * 
     * @param string $type Digits type (decimal, hexadecimal, etc...)
     * @param int    $max  Maximum number of digits to match
     * 
     * @return string|null
     */
    protected function consumeNumbers($type = "", $max = null)
    {
        $buffer = "";
        $char = $this->charAt();
        $count = 0;
        while (in_array($char, $this->{$type . "numbers"})) {
            $buffer .= $char;
            $this->index++;
            $this->column++;
            $count ++;
            if ($count === $max) {
                break;
            }
            $char = $this->charAt();
        }
        return $count ? $buffer : null;
    }
    
    /**
     * Consumes the exponent part of a number
     * 
     * @return string|null
     */
    protected function consumeExponentPart()
    {
        $buffer = "";
        $char = $this->charAt();
        if (strtolower($char) === "e") {
            $this->index++;
            $this->column++;
            $buffer .= $char;
            $char = $this->charAt();
            if ($char === "+" || $char === "-") {
                $this->index++;
                $this->column++;
                $buffer .= $char;
            }
            $tempBuffer = $this->consumeNumbers();
            if ($tempBuffer === null) {
                return $this->error("Missing exponent");
            }
            $buffer .= $tempBuffer;
        }
        return $buffer;
    }
    
    /**
     * Punctutator scanning method
     * 
     * @return Token|null
     */
    protected function scanPunctutator()
    {
        $bestMatch = null;
        $consumed = 1;
        $char = $this->charAt();
        
        //Check if the next char is a bracket
        if (isset($this->brackets[$char])) {
            //Check if it is a closing bracket
            if ($this->brackets[$char]) {
                $openBracket = $this->brackets[$char];
                //Check if there is a corresponding open bracket
                if (!isset($this->openBrackets[$openBracket]) ||
                    !$this->openBrackets[$openBracket]
                ) {
                    if (!$this->isAfterSlash()) {
                        return $this->error();
                    }
                } else {
                    $this->openBrackets[$openBracket]--;
                }
            } else {
                if (!isset($this->openBrackets[$char])) {
                    $this->openBrackets[$char] = 0;
                }
                $this->openBrackets[$char]++;
            }
            $bestMatch = array($consumed, $char);
        } elseif (isset($this->punctutatorsMap[$char])) {
            //If the character is a valid punctutator, first check if the
            //punctutators map for that character has a max length of 1 and in
            //that case match immediatelly
            if ($this->punctutatorsMap[$char]["maxLen"] === 1) {
                $bestMatch = array($consumed, $char);
            } else {
                //Otherwise consume a number of characters equal to the max
                //length and find the longest match
                $buffer = $char;
                $map = $this->punctutatorsMap[$char]["map"];
                $maxLen = $this->punctutatorsMap[$char]["maxLen"];
                do {
                    if (in_array($buffer, $map)) {
                        $bestMatch = array($consumed, $buffer);
                    }
                    $buffer .= $this->charAt($this->index + $consumed);
                    $consumed++;
                } while ($consumed <= $maxLen);
            }
        } else {
            return null;
        }
        
        $this->index += $bestMatch[0];
        $this->column += $bestMatch[0];
        return new Token(Token::TYPE_PUNCTUTATOR, $bestMatch[1]);
    }
    
    /**
     * Keywords and identifiers scanning method
     * 
     * @return Token|null
     */
    protected function scanKeywordOrIdentifier()
    {
        //Consume the maximum number of characters that are unicode escape
        //sequences or valid identifier starts (only the first character) or
        //parts
        $buffer = "";
        $fn = "isIdentifierStart";
        while (($char = $this->charAt()) !== null) {
            if ($this->$fn($char)) {
                $buffer .= $char;
                $this->index++;
                $this->column++;
            } elseif ($seq = $this->consumeUnicodeEscapeSequence()) {
                //Verify that is a valid character
                if (!$this->$fn($seq)) {
                    break;
                }
                $buffer .= $seq;
            } else {
                break;
            }
            $fn = "isIdentifierPart";
        }
        
        //Identify token type
        if ($buffer === "") {
            return null;
        } elseif ($buffer === "null") {
            $type = Token::TYPE_NULL_LITERAL;
        } elseif ($buffer === "true" || $buffer === "false") {
            $type = Token::TYPE_BOOLEAN_LITERAL;
        } elseif (in_array($buffer, $this->keywords) ||
            in_array($buffer, $this->strictModeKeywords)
        ) {
            $type = Token::TYPE_KEYWORD;
        } else {
            $type = Token::TYPE_IDENTIFIER;
        }
        
        return new Token($type, $buffer);
    }
    
    /**
     * Consumes an unicode escape sequence
     * 
     * @return string|null
     */
    protected function consumeUnicodeEscapeSequence()
    {
        $char = $this->charAt();
        if ($char !== "\\" ||
            ($nextChar = $this->charAt($this->index + 1)) !== "u") {
            return null;
        }
        
        $startIndex = $this->index;
        $startColumn = $this->column;
        $this->index += 2;
        $this->column += 2;
        if ($this->charAt() === "{") {
            //\u{FFF}
            $this->index++;
            $this->column++;
            $code = $this->consumeNumbers("x");
            if ($code && $this->charAt() !== "}") {
                $code = null;
            } else {
                $this->index++;
                $this->column++;
            }
        } else {
            //\uFFFF
            $code = $this->consumeNumbers("x", 4);
            if ($code && strlen($code) !== 4) {
                $code = null;
            }
        }
        
        //Unconsume everything if the format is invalid
        if ($code === null) {
            $this->index = $startIndex;
            $this->column = $startColumn;
            return null;
        }
        
        //Return the decoded character
        return Utils::unicodeToUtf8(hexdec($code));
    }
    
    /**
     * Checks if the given character is a valid identifier start
     * 
     * @param string $char Character to check
     * 
     * @return bool
     */
    protected function isIdentifierStart($char)
    {
        return ($char >= "a" && $char <= "z") ||
               ($char >= "A" && $char <= "Z") ||
               $char === "_" || $char === "$" ||
               preg_match($this->idStartRegex, $char);
    }
    
    /**
     * Checks if the given character is a valid identifier part
     * 
     * @param string $char Character to check
     * 
     * @return bool
     */
    protected function isIdentifierPart($char)
    {
        return ($char >= "a" && $char <= "z") ||
               ($char >= "A" && $char <= "Z") ||
               ($char >= "0" && $char <= "9") ||
               $char === "_" || $char === "$" ||
               preg_match($this->idPartRegex, $char);
    }
    
    /**
     * Increases columns and lines count according to the given string
     * 
     * @param string $buffer String to analyze
     * 
     * @return void
     */
    protected function adjustColumnAndLine($buffer)
    {
        $lines = preg_split($this->linesSplitter, $buffer);
        $linesCount = count($lines) - 1;
        $this->line += $linesCount;
        $columns = mb_strlen($lines[$linesCount], "UTF-8");
        if ($linesCount) {
            $this->column = $columns;
        } else {
            $this->column += $columns;
        }
    }
    
    /**
     * Consumes characters until one of the given characters is found
     * 
     * @param array $stops Characters to search
     * 
     * @return string|null
     */
    protected function consumeUntil($stops)
    {
        $buffer = "";
        $escaped = false;
        while (($char = $this->charAt()) !== null) {
            $this->index++;
            $buffer .= $char;
            if (!$escaped && in_array($char, $stops)) {
                $this->adjustColumnAndLine($buffer);
                return array($buffer, $char);
            } elseif (!$escaped && $char === "\\") {
                $escaped = true;
            } else {
                $escaped = false;
            }
        }
        return null;
    }
}