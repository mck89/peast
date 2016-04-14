<?php
namespace Peast\Syntax;

class Scanner
{
    protected $column = 0;
    
    protected $line = 1;
    
    protected $index = 0;
    
    protected $length;
    
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
        if ($encoding && !preg_match("/UTF-?8/i", $encoding)) {
            $source = mb_convert_encoding($source, "UTF-8", $encoding);
        }
        $this->chars = preg_split('/(?<!^)(?!$)/u', $source);
        $this->length = count($this->chars);
    }
    
    public function setConfig(Config $config)
    {
        $symbolMap = "";
        $this->symbols = array();
        $this->maxSymbolLength = -1;
        foreach ($config["symbols"] as $config->getSymbols) {
            $symbolMap .= $symbol;
            $len = strlen($symbol);
            $this->maxSymbolLength = max($len, $this->maxSymbolLength);
            if (!isset($this->symbols[$len])) {
                $this->symbols[$len] = array();
            }
            $this->symbols[$len][] = $symbol;
        }
        $this->symbolChars = array_unique(explode("", $symbolMap));
        
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
        return $this;
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
            $this->index = $index;
            return array($buffer, $len);
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
        if ($buffer !== "") {
            for ($len = $bufferLen; $i > 0; $i--) {
                if (!isset($this->symbols[$len]) ||
                    !in_array($buffer, $this->symbols[$len])) {
                    $bufferLen--;
                    $buffer = substr($buffer, 0, $bufferLen);
                }
                break;
            }
            if ($buffer !== "") {
                $this->index += $bufferLen;
                return array($buffer, $bufferLen);
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
            $this->index = $index;
            return array($buffer, $len);
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
    
    protected function getToken()
    {
        if ($this->index < $this->length) {
            if (($source = $this->scanWhitespaces()) !== null) {
                $lines = $this->splitLines($source[0]);
                return array(
                    "source" => $lines,
                    "length" => $source[1],
                    "whitespace" => true
                );
            } elseif(($source = $this->scanSymbol()) !== null) {
                return array(
                    "source" => $source[0],
                    "length" => $source[1],
                    "whitespace" => false
                );
            } elseif(($source = $this->scanOther()) !== null) {
                return array(
                    "source" => $source[0],
                    "length" => $source[1],
                    "whitespace" => false
                );
            }
        }
        return null;
    }
    
    protected function consumeToken($token)
    {
        if ($token["whitespace"]) {
            $linesCount = count($token["source"]) - 1;
            $this->line += $linesCount;
            $this->column += mb_strlen($token["source"][$linesCount]);
        } else {
            $this->column += $token["length"];
        }
    }
    
    protected function unconsumeToken($token)
    {
        $this->index -= $token["length"];
    }
    
    public function consumeWhitespacesAndComments($lineTerminator = true)
    {
        if (!$lineTerminator) {
            $position = $this->getPosition();
        }
        $comment = $processed = 0;
        while ($token = $this->getToken()) {
            $processed++;
            $source = $token["source"];
            if ($token["whitespace"]) {
                if (count($source) > 1) {
                    if (!$lineTerminator) {
                        $this->setPosition($position);
                        return false;
                    } elseif ($comment === 1) {
                        $comment = 0;
                    }
                }
                $this->consumeToken($token);
            } elseif (!$comment && $source === "//") {
                $comment = 1;
                $this->consumeToken($token);
            }elseif (!$comment && $source === "/*") {
                $comment = 2;
                $this->consumeToken($token);
            } elseif ($comment === 2 && $source === "*/") {
                $comment = 0;
                $this->consumeToken($token);
            } else {
                $this->unconsumeToken($token);
                return $processed > 1;
            }
        }
        return false;
    }
    
    public function consume($string)
    {
        $this->consumeWhitespacesAndComments();
        
        $token = $this->getToken();
        if (!$token || $token["source"] !== $string) {
            $this->unconsumeToken($token);
            return false;
        }
        
        $this->consumeToken($token);
        
        return true;
    }
    
    public function consumeIdentifier()
    {
        $this->consumeWhitespacesAndComments();
        
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
            } elseif ($start &&
                      preg_match($this->config->getIdRegex(), $char)) {
                $index++;
                $buffer .= $char;
            } elseif (!$start &&
                      preg_match($this->config->getIdRegex(true), $char)) {
                $index++;
                $buffer .= $char;
            } elseif ($char === "\\" &&
                      isset($this->chars[$index + 1]) &&
                      $this->chars[$index + 1] === "u") {
                //UnicodeEscapeSequence
                $valid = true;
                $subBuffer = "\\u";
                if (isset($this->chars[$index + 2]) &&
                    $this->chars[$index + 2] === "{" &&
                    isset($this->chars[$index + 3])) {
                    
                    $oneMatched = false;
                    $subBuffer .= "{";
                    for ($i = $index + 4; $i < $this->length; $i++) {
                        if ($this->isHexDigit($this->chars[$index])) {
                            $oneMatched = true;
                            $subBuffer .= $this->chars[$index];
                        } elseif ($oneMatched && $this->chars[$index] === "}") {
                            $subBuffer .= $this->chars[$index];
                            break;
                        } else {
                            $valid = false;
                            break;
                        }
                    }
                    
                } else {
                    for ($i = $index + 3; $i <= $index + 7; $i++) {
                        if (isset($this->chars[$i]) &&
                            $this->isHexDigit($this->chars[$index])) {
                            $subBuffer .= $this->chars[$i];
                        } else {
                            $valid = false;
                            break;
                        }
                    }
                }
                
                if (!$valid) {
                    break;
                }
                
                $buffer .= $subBuffer;
                $index += strlen($subBuffer);
                
            } else {
                break;
            }
            $start = false;
        }
        
        if ($buffer !== "") { 
            $this->column += $index - $this->index;
            $this->index = $index; 
            return $buffer;
        }
        
        return null;
    }
    
    public function consumeArray($sequence)
    {
        $position = $this->getPosition();
        foreach ($sequence as $string) {
            if ($this->consume($string) === false) {
                $this->setPosition($position);
                return false;
            }
        }
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
    
    public function conumeOneOf($tests)
    {
        foreach ($tests as $test) {
            if ($this->scanner->consume($test)) {
                return $test;
            }
        }
        return null;
    }
    
    public function consumeUntil($stop, $allowLineTerminator = true)
    {
        foreach ($stop as $s) {
            $stopMap[$s[0]] = array(strlen($s), $s);
        }
    	$index = $this->index;
    	$escaped = false;
    	$buffer = "";
    	$lineTerminators = $this->config->getLineTerminators();
    	$valid = false;
    	while ($this->index < $this->length) {
    		$char = $this->chars[$index];
    		$buffer .= $char;
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
    		    $seq = array_slice($this->chars, $index, $len);
    		    if (implode("", $seq) === $stopMap[$char][1]) {
    		        $valid = true;
    		        break;
    		    }
    		}
    		$index++;
    	}
    	
    	if (!$valid) {
    	    return null;
    	}
    	
	    if (!$lineTerminators) {
	        $this->column += ($index - $this->index);
	    } else {
	        $lines = $this->splitLines($buffer);
	        $linesCount = count($lines) - 1;
	        $this->line += $linesCount;
            $this->column += mb_strlen($lines[$linesCount]);
	    }
	    $this->index = $index;
	    
	    return $buffer;
    }
}