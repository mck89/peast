<?php
namespace Peast;

class Peast
{
    const SOURCE_TYPE_SCRIPT = "script";
    
    const SOURCE_TYPE_MODULE = "module";
    
    static public function ES6($source, $options = array())
    {
        $parser = new Syntax\ES6\Parser($options);
        return $parser->setSource($source);
    }
}