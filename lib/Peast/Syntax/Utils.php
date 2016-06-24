<?php
namespace Peast\Syntax;

class Utils
{
    /**
     * @codeCoverageIgnore
     */
    static public function unicodeToUtf8($num)
    {
        //From: http://stackoverflow.com/questions/1805802/php-convert-unicode-codepoint-to-utf-8
        if($num <= 0x7F) {
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
    
    protected static $lineTerminatorsCache;
    
    /**
     * @codeCoverageIgnore
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
    
    static public function unquoteLiteralString($str)
    {
        //Remove quotes
        $str = substr($str, 1, -1);
        
        $lineTerminators = self::getLineTerminators();
        
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
    
    static public function quoteLiteralString($str, $quote)
    {
        $escape = self::getLineTerminators();
        $escape[] = $quote;
        $escape[] = "\\\\";
        $reg = "/(" . implode("|", $escape) . ")/";
        $str = preg_replace($reg, "\\$1", $str);
        return $quote . $str . $quote;
    }
}