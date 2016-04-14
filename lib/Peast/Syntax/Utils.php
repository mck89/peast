<?php
namespace Peast\Syntax;

class Utils
{
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
}