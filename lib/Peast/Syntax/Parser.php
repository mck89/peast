<?php
namespace Peast\Syntax;

abstract class Parser
{
    protected $scanner;
    
    abstract public function parse();
    
    abstract public function setSource($source, $encoding = null);
    
    public function createNode($nodeType, $position)
    {
        $parts = explode("\\", get_class($this));
        array_pop($parts);
        $nodeClass = implode("\\", $parts) . "\\Node\\$nodeType";
        $node = new $nodeClass;
        if ($position instanceof Node || $position instanceof Token) {
            $position = $position->getLocation()->getStart();
        } elseif (is_array($position)) {
            if (count($position)) {
                $position = $position[0]->getLocation()->getStart();
            } else {
                $position = $this->scanner->getPosition();
            }
        }
        return $node->setStartPosition($position);
    }
    
    public function completeNode(Node $node, $position = null)
    {
        return $node->setEndPosition(
            $position ? $position : $this->scanner->getPosition()
        );
    }
    
    public function error($message = "", $position = null)
    {
        if (!$position) {
            $position = $this->scanner->getPosition();
        }
        if (!$message) {
            $token = $this->scanner->getToken();
            if ($token === null) {
                $message = "Unexpected end of input";
            } else {
                $message = "Unexpected: " . $token->getValue();
            }
        }
        throw new Exception($message, $position);
    }
    
    protected function assertEndOfStatement()//TODO
    {
        $position = $this->scanner->getPosition();
        if ($this->scanner->consumeWhitespacesAndComments(false) === null) {
            $this->scanner->setPosition($position);
            return true;
        } else {
            if ($this->scanner->isEOF() ||
                $this->scanner->consume(";")) {
                return true;
            } elseif ($this->scanner->consume("}")) {
                $this->scanner->setPosition($position);
                return true;
            }
        }
        return $this->error();
    }
    
    protected function charSeparatedListOf($fn, $args = array(), $char = ",")
    {
        $list = array();
        $valid = true;
        while ($param = call_user_func_array(array($this, $fn), $args)) {
            $list[] = $param;
            $valid = true;
            if (!$this->scanner->consume($char)) {
                break;
            } else {
                $valid = false;
            }
        }
        if (!$valid) {
            $this->error();
            return null;
        }
        return $list;
    }
    
    static public function unquoteLiteralString($str)//TODO
    {
        //Remove quotes
        $str = substr($str, 1, -1);
        
        $config = static::getConfig();
        $lineTerminators = $config->getLineTerminators();
        
        //Handle escapes
        $patterns = array(
            "u\{[a-fA-F0-9]+\}",
            "u[a-fA-F0-9]{1,4}",
            "x[a-fA-F0-9]{1,2}",
            "0[0-7]{2}",
            "[1-7][0-7]",
            "."
        );
        $reg = "/\\\\(" . implode("|", $patterns) . ")/s";
        $simpleSequence = array(
            "n" => "\n",
            "f" => "\f",
            "r" => "\r",
            "t" => "\t",
            "v" => "\v",
            "b" => "\x8"
        );
        $replacement = function ($m) use ($simpleSequence, $lineTerminators) {
            $type = $m[1][0];
            if (isset($simpleSequence[$type])) {
                // \n, \r, \t ...
                return $simpleSequence[$type];
            } elseif ($type === "u" || $type === "x") {
                // \uFFFF, \u{FFFF}, \xFF
                $code = substr($m[1], 1);
                $code = str_replace(array("{", "}"), "", $code);
                return Utils::unicodeToUtf8(hexdec($code));
            } elseif ($type >= "0" && $type <= "7") {
                // \123
                return Utils::unicodeToUtf8(octdec($m[1]));
            } elseif (in_array($m[1], $lineTerminators)) {
                // Escaped line terminators
                return "";
            } else {
                // Escaped characters
                return $m[1];
            }
        };
        $str = preg_replace_callback($reg, $replacement, $str);
        
        return $str;
    }
    
    static public function quoteLiteralString($str, $quote)//TODO
    {
        $config = static::getConfig();
        $escape = $config->getLineTerminators();
        $escape[] = $quote;
        $escape[] = "\\\\";
        $reg = "/(" . implode("|", $escape) . ")/";
        $str = preg_replace($reg, "\\$1", $str);
        return $quote . $str . $quote;
    }
}