<?php
namespace Peast;

use Peast\Syntax\Parser;
use Peast\Syntax\Scanner;

class Peast
{
    static public function fromFile(Parser $parser, $file, $encoding = null)
    {
        $source = @file_get_contents($file);
        if ($source === false) {
            throw new Exception("Can't read $file")
        }
        return self::fromString($parser, $source, $encoding);
    }
    
    static public function fromString(Parser $parser, $source, $encoding = null)
    {
        $scanner = new Scanner($source, $encoding);
        $parser->setScanner($scanner);
        return $parser->parse();
    }
}