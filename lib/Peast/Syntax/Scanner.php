<?php
namespace Peast\Syntax;

class Scanner
{
    protected $column = 0;
    
    protected $line = 1;
    
    protected $index = 0;
    
    protected $length;
    
    protected $consumedTokenPosition;
    
    protected $wsCache;
    
    protected $chars = array();
    
    protected $config;
    
    protected $symbols = array();
    
    protected $symbolChars = array();
    
    protected $maxSymbolLength;
    
    protected $lineTerminatorsSplitter;
    
    protected $hexDigits = array(
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
        "a", "b", "c", "d", "e", "f",
        "A", "B", "C", "D", "E", "F"
    );
    
    function __construct($source, $encoding = null)
    {
        if (!$encoding) {
            $encoding = mb_detect_encoding($source);
        }
        
        if ($encoding && !preg_match("/UTF-?8/i", $encoding)) {
            $source = mb_convert_encoding($source, "UTF-8", $encoding);
        }
        
        $this->chars = preg_split('/(?<!^)(?!$)/u', $source);
        $this->length = count($this->chars);
    }
    
    public function setConfig(Config $config)
    {
        $symbolMap = array();
        $this->symbols = array();
        $this->maxSymbolLength = -1;
        foreach ($config->getSymbols() as $symbol) {
            $symbolMap[] = $symbol;
            $len = strlen($symbol);
            $this->maxSymbolLength = max($len, $this->maxSymbolLength);
            if (!isset($this->symbols[$len])) {
                $this->symbols[$len] = array();
            }
            $this->symbols[$len][] = $symbol;
        }
        $this->symbolChars = array_unique($symbolMap);
        
        $terminatorsSeq = implode("|", $config->getLineTerminatorsSequences());
        $this->lineTerminatorsSplitter = "/$terminatorsSeq/u";
        
        $this->config = $config;
        
        return $this;
    }
    
    public function getColumn()
    {
        return $this->column;
    }
    
    public function getLine()
    {
        return $this->line;
    }
    
    public function getIndex()
    {
        return $this->index;
    }
    
    public function getPosition()
    {
        return new Position(
            $this->getLine(),
            $this->getColumn(),
            $this->getIndex()
        );
    }
    
    public function setPosition(Position $position)
    {
        $this->line = $position->getLine();
        $this->column = $position->getColumn();
        $this->index = $position->getIndex();
        $this->clearCache();
        return $this;
    }
    
    public function getConsumedTokenPosition()
    {
        return $this->consumedTokenPosition;
    }
    
    public function isEOF()
    {
        return $this->index >= $this->length;
    }
    
    protected function clearCache()
    {
        $this->wsCache = null;
        $this->consumedTokenPosition = null;
    }
    
    protected function isWhitespace($char)
    {
        return in_array($char, $this->config->getWhitespaces(), true);
    }
    
    protected function scanWhitespaces()
    {
        $index = $this->index;
        $buffer = "";
        while ($index < $this->length) {
            $char = $this->chars[$index];
            if ($this->isWhitespace($char)) {
                $buffer .= $char;
                $index++;
            } else {
                break;
            }
        }
        if ($buffer !== "") {
            $len = $index - $this->index;
            return array(
                "source" => $this->splitLines($buffer),
                "length" => $len,
                "whitespace" => true
            );
        }
        return null;
    }
    
    protected function isSymbol($char)
    {
        return in_array($char, $this->symbolChars);
    }
    
    protected function scanSymbols()
    {
        $index = $this->index;
        $buffer = "";
        $bufferLen = 0;
        while ($index < $this->length && $bufferLen < $this->maxSymbolLength) {
            $char = $this->chars[$index];
            if ($this->isSymbol($char)) {
                $buffer .= $char;
                $index++;
                $bufferLen++;
            } else {
                break;
            }
        }
        if ($bufferLen) {
            while ($bufferLen > 0) {
                if (!isset($this->symbols[$bufferLen]) ||
                    !in_array($buffer, $this->symbols[$bufferLen])) {
                    $bufferLen--;
                    $buffer = substr($buffer, 0, $bufferLen);
                } else {
                    break;
                }
            }
            if ($bufferLen) {
                return array(
                    "source" => $buffer,
                    "length" => $bufferLen,
                    "whitespace" => false
                );
            }
        }
        return null;
    }
    
    protected function scanOther()
    {
        $index = $this->index;
        $buffer = "";
        while ($index < $this->length) {
            $char = $this->chars[$index];
            if (!$this->isWhitespace($char) && !$this->isSymbol($char)) {
                $buffer .= $char;
                $index++;
            } else {
                break;
            }
        }
        if ($buffer !== "") {
            $len = $index - $this->index;
            return array(
                "source" => $buffer,
                "length" => $len,
                "whitespace" => false
            );
        }
        return null;
    }
    
    protected function splitLines($str)
    {
        return preg_split($this->lineTerminatorsSplitter, $str);
    }
    
    protected function isHexDigit($char)
    {
        return in_array($char, $this->hexDigits, true);
    }
    
    public function getToken()
    {
        if (!$this->isEOF()) {
            if ($source = $this->scanWhitespaces()) {
                return $source;
            } elseif ($source = $this->scanSymbols()) {
                return $source;
            } elseif ($source = $this->scanOther()) {
                return $source;
            }
        }
        return null;
    }
    
    protected function consumeToken($token)
    {
        $this->index += $token["length"];
        if ($token["whitespace"]) {
            $linesCount = count($token["source"]) - 1;
            $this->line += $linesCount;
            $columns = mb_strlen($token["source"][$linesCount]);
            if ($linesCount === 0) {
                $this->column += $columns;
            } else {
                $this->column = $columns;
            }
        } else {
            $this->column += $token["length"];
        }
    }
    
    public function consumeWhitespacesAndComments($lineTerminator = true)
    {
        if (!$lineTerminator) {
            $position = $this->getPosition();
        } elseif ($this->wsCache) {
            $this->setPosition($this->wsCache[0]);
            return $this->wsCache[1];
        }
        $comment = $processed = 0;
        while ($token = $this->getToken()) {
            $processed++;
            $source = $token["source"];
            if ($token["whitespace"]) {
                if (count($source) > 1) {
                    if (!$lineTerminator) {
                        $this->setPosition($position);
                        return null;
                    } elseif ($comment === 1) {
                        $comment = 0;
                    }
                }
                $this->consumeToken($token);
            } elseif (!$comment && $source === "//") {
                $comment = 1;
                $this->consumeToken($token);
            } elseif (!$comment && $source === "/*") {
                $comment = 2;
                $this->consumeToken($token);
            } elseif ($comment === 2 && $source === "*/") {
                $comment = 0;
                $this->consumeToken($token);
            } elseif ($comment) {
                $this->consumeToken($token);
            } else {
                return $processed > 1;
            }
        }
        return $comment ? null : $processed;
    }
    
    public function consume($string)
    {
        //Store current position so that it can be restored if the token does
        //not match
        $initPosition = $this->getPosition();
        
        //Consume any whitespace and comment before checking
        $ws = $this->consumeWhitespacesAndComments();
        
        //Store the position after whitespaces and comments
        $trimmedPosition = $this->getPosition();
        
        //Check the token
        $token = $this->getToken();
        if (!$token || $token["source"] !== $string) {
            //Token does not match. Get back to initial position and fill the
            //whitespaces cache
            $this->setPosition($initPosition);
            $this->wsCache = array($trimmedPosition, $ws);
            return false;
        }
        
        //The token matches so consumedTokenPosition becomes the position after
        //whitespaces and the whitespaces cache must be cleared
        $this->clearCache();
        $this->consumedTokenPosition = $trimmedPosition;
        
        //Finally consume the matching token
        $this->consumeToken($token);
        
        return true;
    }
    
    public function consumeIdentifier()
    {
        $postion = $this->getPosition();
        
        $ws = $this->consumeWhitespacesAndComments();
        
        $trimmedPosition = $this->getPosition();
        $this->clearCache();
        
        $start = true;
        $index = $this->index;
        $buffer = "";
        while ($index < $this->length) {
            $char = $this->chars[$index];
            if ($char === "$" || $char === "_" ||
                ($char >= "A" && $char <= "Z") ||
                ($char >= "a" && $char <= "z") ||
                (!$start && $char >= "0" && $char <= "9")) {
                $index++;
                $buffer .= $char;
            } elseif ($char === "\\" &&
                      isset($this->chars[$index + 1]) &&
                      $this->chars[$index + 1] === "u") {
                //UnicodeEscapeSequence
                $index += 2;
                $valid = true;
                $subBuffer = "";
                if (isset($this->chars[$index]) &&
                    $this->chars[$index] === "{" &&
                    isset($this->chars[$index + 1])) {
                    
                    $index++;
                    $oneMatched = false;
                    for ($i = $index; $i < $this->length; $i++) {
                        if ($this->isHexDigit($this->chars[$i])) {
                            $oneMatched = true;
                            $subBuffer .= $this->chars[$i];
                        } elseif ($oneMatched && $this->chars[$i] === "}") {
                            $index++;
                            break;
                        } else {
                            $valid = false;
                            break;
                        }
                    }
                    
                } else {
                    
                    for ($i = $index; $i <= $index + 3; $i++) {
                        if (isset($this->chars[$i]) &&
                            $this->isHexDigit($this->chars[$i])) {
                            $subBuffer .= $this->chars[$i];
                        } else {
                            $valid = false;
                            break;
                        }
                    }
                }
                
                if (!$subBuffer) {
                    $valid = false;
                } elseif ($valid) {
                    $decodedChar = Utils::unicodeToUtf8(hexdec($subBuffer));
                    $valid = preg_match(
                        $this->config->getIdRegex(!$start), $decodedChar
                    );
                }
                
                if (!$valid) {
                    $buffer = "";
                    break;
                }
                
                $buffer .= $decodedChar;
                $index += strlen($subBuffer);
                
            } elseif (preg_match($this->config->getIdRegex(!$start), $char)) {
                $index++;
                $buffer .= $char;
            } else {
                break;
            }
            $start = false;
        }
        
        if ($buffer !== "") {
            $this->consumedTokenPosition = $trimmedPosition;
            $this->column += $index - $this->index;
            $this->index = $index; 
            return $buffer;
        } else {
            $this->setPosition($postion);
            $this->wsCache = array($trimmedPosition, $ws);
        }
        
        return null;
    }
    
    public function consumeRegularExpression()
    {
        $postion = $this->getPosition();
        
        $ws = $this->consumeWhitespacesAndComments();
        
        $trimmedPosition = $this->getPosition();
        $this->clearCache();
        
        if ($this->index + 1 < $this->length &&
            $this->chars[$this->index] === "/" &&
            !in_array($this->chars[$this->index + 1], array("/", "*"), true)) {
            
            $this->index++;
            $this->column++;
            
            $inClass = false;
            $source = "/";
            $valid = true;
            while (true) {
                if ($inClass) {
                    $sub = $this->consumeUntil(array("]"), false);
                    if (!$sub) {
                        $valid = false;
                        break;
                    } else {
                        $source .= $sub;
                        $inClass = false;
                    }
                } else {
                    $sub = $this->consumeUntil(array("[", "/"), false);
                    if (!$sub) {
                        $valid = false;
                        break;
                    } else {
                        $source .= $sub;
                        $lastChar = substr($sub, -1);
                        if ($lastChar === "/") {
                            break;
                        } else {
                            $inClass = true;
                        }
                    }
                }
            }
            
            if (!$inClass && $valid) {
                while (!$this->isEOF()) {
                    $char = $this->chars[$this->index];
                    if ($char >= "a" && $char <= "z") {
                        $source .= $char;
                        $this->index++;
                        $this->column++;
                    } else {
                        break;
                    }
                }
                $this->consumedTokenPosition = $trimmedPosition;
                return $source;
            }
        }
        
        $this->setPosition($postion);
        $this->wsCache = array($trimmedPosition, $ws);
        
        return null;
    }
    
    public function consumeNumber()
    {
        $postion = $this->getPosition();
        
        $ws = $this->consumeWhitespacesAndComments();
        
        $trimmedPosition = $this->getPosition();
        $this->clearCache();
        
        $decimalExp = true;
        $source = "";
        
        $reset = function () use ($postion, $trimmedPosition, $ws) {
            $this->setPosition($postion);
            $this->wsCache = array($trimmedPosition, $ws);
            return null;
        };
        
        $checkNoDecimal = function ($s) use ($reset, $trimmedPosition) {
            $sym = $this->scanSymbols();
            if (!$sym || $sym["source"] !== ".") {
                $this->consumedTokenPosition = $trimmedPosition;
                return $s;
            } else {
                return $reset();
            }
        };
        
        $handleExponent = function ($n, $allowEmptyBase = false) use ($reset) {
            $parts = preg_split("/e/i", $n);
            if (count($parts) > 2 ||
                (!preg_match("/^\d+$/", $parts[0]) && (
                !$allowEmptyBase || $parts[0] !== ""))) {
                return $reset();
            } elseif (!isset($parts[1])) {
                return false;
            }
            $expPart = $parts[1];
            $ret = "";
            if ($expPart === "") {
                $sign = $this->scanSymbols();
                if (!$sign ||
                    ($sign["source"] !== "+" && $sign["source"] !== "-")) {
                    return $reset();
                }
                $this->consumeToken($sign);
                $expNum = $this->scanOther();
                $this->consumeToken($expNum);
                $expPart = $expNum["source"];
                $ret = $sign["source"] . $expPart;
            }
            if (!preg_match("/^\d+$/", $expPart)) {
                return $reset();
            }
            return $ret;
        };
        
        $nextChar = !$this->isEOF() ?
                    $this->chars[$this->index] :
                    null;
        if (!(($nextChar >= "0" && $nextChar <= "9") || $nextChar === ".")) {
            return $reset();
        }
        
        if ($num = $this->scanOther()) {
            $this->consumeToken($num);
            $source = $num["source"];
            if ($source[0] === "0" && isset($source[1])) {
                $char = strtolower($source[1]);
                if ($char === "b") {
                    //Binary form
                    if (!$this->config->supportsBinaryNumberForm() ||
                        !preg_match("/^0[bB][01]+$/", $source)) {
                        return $reset();
                    }
                    return $checkNoDecimal($source);
                } elseif ($char === "o") {
                    //Octal form
                    if (!$this->config->supportsOctalNumberForm() ||
                        !preg_match("/^0[oO][0-7]+$/", $source)) {
                        return $reset();
                    }
                    return $checkNoDecimal($source);
                } elseif ($char === "x") {
                    //Hexadecimal form
                    if (!preg_match("/^0[xX][0-9a-fA-F]+$/", $source)) {
                        return $reset();
                    }
                    return $checkNoDecimal($source);
                } elseif (preg_match("/^0[0-7]+/", $source)) {
                    //Implicit octal form
                    return $checkNoDecimal($source);
                }
            }
            $exp = $handleExponent($num["source"]);
            if ($exp === null) {
                return $reset();
            } elseif ($exp) {
                $source .= $exp;
            }
            $decimalExp = $exp === false;
        }
        
        //Validate decimal part
        $dot = $this->scanSymbols();
        if ($dot && $dot["source"] === ".") {
            $this->consumeToken($dot);
            $source .= ".";
            if ($decPart = $this->scanOther()) {
                $this->consumeToken($decPart);
                $source .= $decPart["source"];
                $exp = $handleExponent($decPart["source"], true);
                if ($exp === null || ($exp !== false && !$decimalExp)) {
                    return $reset();
                } elseif ($exp) {
                    $source .= $exp;
                }
            } elseif ($source === ".") {
                return $reset(); 
            }
        }
        
        return $checkNoDecimal($source);
    }
    
    public function consumeArray($sequence)
    {
        $position = $this->getPosition();
        $firstConsumedTokenPosition = null;
        foreach ($sequence as $string) {
            if ($this->consume($string) === false) {
                $this->setPosition($position);
                return false;
            }
            if (!$firstConsumedTokenPosition) {
                $firstConsumedTokenPosition = $this->consumedTokenPosition;
            }
        }
        $this->consumedTokenPosition = $firstConsumedTokenPosition;
        return true;
    }
    
    public function notBefore($tests)
    {
        $position = $this->getPosition();
        foreach ($tests as $test) {
            $testFn = is_array($test) ? "consumeArray" : "consume";
            if ($this->$testFn($test)) {
                $this->setPosition($position);
                return false;
            }
        }
        return true;
    }
    
    public function consumeOneOf($tests)
    {
        foreach ($tests as $test) {
            if ($this->consume($test)) {
                return $test;
            }
        }
        return null;
    }
    
    public function consumeUntil($stop, $allowLineTerminator = true)
    {
        $this->clearCache();
        foreach ($stop as $s) {
            $stopMap[$s[0]] = array(strlen($s), $s);
        }
    	$index = $this->index;
    	$escaped = false;
    	$buffer = "";
    	$lineTerminators = $this->config->getLineTerminators();
    	$valid = false;
    	while ($index < $this->length) {
    		$char = $this->chars[$index];
    		$buffer .= $char;
    		$index++;
    		if ($escaped) {
    		    $escaped = false;
    		} elseif ($char === "\\") {
    		    $escaped = true;
    		} elseif (!$allowLineTerminator &&
    		          in_array($char, $lineTerminators, true)) {
    		    break;
    		} elseif (isset($stopMap[$char])) {
    		    $len = $stopMap[$char][0];
    		    if ($len === 1) {
    		        $valid = true;
    		        break;
    		    }
    		    $seq = array_slice($this->chars, $index - 1, $len);
    		    if (implode("", $seq) === $stopMap[$char][1]) {
                    $buffer .= substr($stopMap[$char][1], 1);
    		        $index += $len - 1;
    		        $valid = true;
    		        break;
    		    }
    		}
    	}
    	
    	if (!$valid) {
    	    return null;
    	}
    	
	    $lines = $this->splitLines($buffer);
        $linesCount = count($lines) - 1;
        $this->line += $linesCount;
        if ($linesCount) {
            $this->column = mb_strlen($lines[$linesCount]);
        } else {
            $this->column += mb_strlen($lines[$linesCount]);
        }
	    $this->index = $index;
	    
	    return $buffer;
    }
}