<?php
namespace Peast;

class Peast
{
    const SOURCE_TYPE_SCRIPT = "script";
    
    const SOURCE_TYPE_MODULE = "module";
    
    static public function ES6($source, $options = array())
    {
        return new Syntax\ES6\Parser($source, $options);
    }
}