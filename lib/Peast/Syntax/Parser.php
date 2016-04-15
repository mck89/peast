<?php
namespace Peast\Syntax;

abstract class Parser
{
    protected $scanner;
    
    public function setScanner(Scanner $scanner)
    {
        $this->scanner = $scanner;
        return $this;
    }
    
    abstract public function parse();
    
    abstract public function getConfig();
    
    public function createNode($nodeType)
    {
        $parts = explode("\\", get_class($this));
        array_pop($parts);
        $nodeClass = implode("\\", $parts) . "\\Node\\$nodeType";
        $node = new $nodeClass;
        return $node->setStartPosition($scanner->getPosition());
    }
    
    public function completeNode(Node $node)
    {
        return $node->setEndPosition($scanner->getPosition());
    }
    
    protected function charSeparatedListOf($fn, $args, $char = ",")
    {
        $multi = is_array($char);
        $list = array();
        $position = $this->scanner->getPosition();
        $valid = true;
        $matchedChar = null;
        while ($param = call_user_func_array(array($this, $fn), $args)) {
            $list[] = $multi ? $param : array($param, $matchedChar);
            $valid = true;
            $matchedChar = $multi ?
                           $this->scanner->consumeOneOf($char) :
                           $this->scanner->consume($char);
            if (!$matchedChar) {
                break;
            } else {
                $valid = false;
            }
        }
        if (!$valid) {
            $this->scanner->setPosition($position);
            return null;
        }
        return $list;
    }
    
    protected function recursiveExpression($fn, $args, $operator, $class)
    {
        $multi = is_array($operator);
        $list = $this->charSeparatedListOf($fn, $args, $operator);
        
        if ($list === null) {
            return null;
        } elseif (count($list) === 1) {
            return $list[0];
        } else {
            $lastNode = null;
            foreach ($list as $i => $expr) {
                if ($i) {
                    $node = $this->createNode($class);
                    $node->setLeft($lastNode ?
                                   $lastNode :
                                   ($multi ? $list[0][0] : $list[0]));
                    $node->setOperator($multi ? $expr[1] : $operator);
                    $node->setRight($multi ? $expr[0] : $multi[1]);
                    $lastNode = $this->completeNode($node);
                }
            }
        }
        
        return $lastNode;
    }
    
    static public function unquoteLiteralString($str)
    {
        //Remove quotes
        $str = substr($str, 1, -1);
        
        //Handle escapes
        $patterns = array(
            "u{[a-fA-F0-9]+\}",
            "u[a-fA-F0-9]{1,4}",
            "x[a-fA-F0-9]{1,2}",
            "[0-7]{1,3}",
            "."
        );
        $reg = "/\\\\(" . implode("|", $patterns) . ")/";
        $simpleSequence = array(
            "n" => "\n",
            "f" => "\f",
            "r" => "\r",
            "t" => "\t",
            "v" => "\v",
            "b" => "\x8"
        );
        $replacement = function ($m) use ($simpleSequence) {
            $type = $m[1][0];
            if (isset($simpleSequence[$type])) {
                // \n, \r, \t ...
                return $simpleSequence[$type];
            } elseif ($type === "u" || $type === "x") {
                // \uFFFF, \u{FFFF}, \xFF
                $code = substr($m[1], 1);
                $code = str_replace(array("{", "}"), "");
                return Utils::unicodeToUtf8(hexdec($code));
            } elseif ($type >= "0" && $type <= "7") {
                // \123
                return Utils::unicodeToUtf8(octdec($code));
            } else {
                // Escaped characters
                return $m[1];
            }
        };
        $str = preg_replace_callback($reg, $replacement, $str);
        
        return $str;
    }
    
    static public function quoteLiteralString($str, $quote)
    {
        $config = self::getConfig();
        $escape = $config->getLineTerminators();
        $escape[] = $quote;
        $escape[] = "\\";
        $reg = "/(" . implode("|", $escape) . ")/";
        $str = preg_replace($reg, "\\$1", $str);
        return $quote . $str . $quote;
    }
}