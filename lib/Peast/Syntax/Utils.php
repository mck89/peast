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
 * Utilities class.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Utils
{
    /**
     * Converts a string to an array of UTF-8 characters
     * 
     * @param string $str String to convert
     * 
     * @return array
     */
    static public function stringToUTF8Array($str)
    {
        return $str === "" ?
               array() :
               preg_split('//u', $str, null, PREG_SPLIT_NO_EMPTY);
    }
    
    /**
     * Converts an unicode code point to UTF-8
     * 
     * @param int $num Unicode code point
     * 
     * @return string
     * 
     * @codeCoverageIgnore
     */
    static public function unicodeToUtf8($num)
    {
        //From: http://stackoverflow.com/questions/1805802/php-convert-unicode-codepoint-to-utf-8#answer-7153133
        if ($num <= 0x7F) {
            return chr($num);
        } elseif ($num <= 0x7FF) {
            return chr(($num >> 6) + 192) .
                   chr(($num & 63) + 128);
        } elseif ($num <= 0xFFFF) {
            return chr(($num >> 12) + 224) .
                   chr((($num >> 6) & 63) + 128) .
                   chr(($num & 63) + 128);
        } elseif ($num <= 0x1FFFFF) {
            return chr(($num >> 18) + 240) .
                   chr((($num >> 12) & 63) + 128) .
                   chr((($num >> 6) & 63) + 128) .
                   chr(($num & 63) + 128);
        }
        return '';
    }
    
    /**
     * Compiled line terminators cache
     * 
     * @var array 
     */
    protected static $lineTerminatorsCache;
    
    /**
     * Returns line terminators array
     * 
     * @return array
     */
    protected static function getLineTerminators()
    {
        if (!self::$lineTerminatorsCache) {
            self::$lineTerminatorsCache = array();
            foreach (Scanner::$lineTerminatorsChars as $char) {
                self::$lineTerminatorsCache[] = is_int($char) ?
                                                self::unicodeToUtf8($char) :
                                                $char;
            }
        }
        return self::$lineTerminatorsCache;
    }
    
    /**
     * This function takes a string as it appears in the source code and returns
     * an unquoted version of it
     * 
     * @param string $str The string to unquote
     * 
     * @return string
     */
    static public function unquoteLiteralString($str)
    {
        //Remove quotes
        $str = substr($str, 1, -1);
        
        $lineTerminators = self::getLineTerminators();
        
        //Handle escapes
        $patterns = array(
            "u\{[a-fA-F0-9]+\}",
            "u[a-fA-F0-9]{4}",
            "x[a-fA-F0-9]{2}",
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
                //Invalid unicode or hexadecimal sequences
                if (strlen($m[1]) === 1) {
                    return "\\$type";
                }
                // \uFFFF, \u{FFFF}, \xFF
                $code = substr($m[1], 1);
                $code = str_replace(array("{", "}"), "", $code);
                return Utils::unicodeToUtf8(hexdec($code));
            } elseif ($type >= "0" && $type <= "7") {
                //Invalid octal sequences
                if (strlen($m[1]) === 1) {
                    return "\\$type";
                }
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
    
    /**
     * This function converts a string to a quoted javascript string
     * 
     * @param string $str   String to quote
     * @param string $quote Quote character
     * 
     * @return string
     */
    static public function quoteLiteralString($str, $quote)
    {
        $escape = self::getLineTerminators();
        $escape[] = $quote;
        $escape[] = "\\\\";
        $reg = "/(" . implode("|", $escape) . ")/";
        $str = preg_replace($reg, "\\\\$1", $str);
        return $quote . $str . $quote;
    }
    
    /**
     * Returns the properties map for the given node
     * 
     * @param mixed $node Node or class to consider
     * 
     * @return array
     */
    static protected function getPropertiesMap($node)
    {
        static $cache = array();
        
        if ($node instanceof \ReflectionClass) {
            $className = $node->getName();
        } else {
            $className = get_class($node);
        }
        
        if (!isset($cache[$className])) {
            $class = new \ReflectionClass($className);
            $parent = $class->getParentClass();
            $props = $parent ? self::getPropertiesMap($parent) : array();
            $defaults = $class->getDefaultProperties();
            if (isset($defaults["propertiesMap"])) {
                $props = array_merge($props, $defaults["propertiesMap"]);
            }
            $cache[$className] = $props;
        }
        return $cache[$className];
    }
    
    /**
     * Returns the properties list for the given node
     * 
     * @param Node\Node $node        Node to consider
     * @param bool      $traversable If true it returns only traversable properties
     * 
     * @return array
     */
    static public function getNodeProperties(Node\Node $node, $traversable = false)
    {
        $props = self::getPropertiesMap($node);
        return array_keys($traversable ? array_filter($props) : $props);
    }
}