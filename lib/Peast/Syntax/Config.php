<?php
namespace Peast\Syntax;

abstract class Config
{
    abstract public function getIdRegex($part = false);
    
    abstract public function getSymbols();
    
    abstract public function getWhitespaces();
    
    abstract public function getLineTerminators();
    
    abstract public function getLineTerminatorsSequences();
    
    protected $compiledUnicodeArray = array();
    
    protected function cachedCompiledUnicodeArray($name)
    {
        if (!isset($this->cachedCompiledUnicodeArray[$name])) {
            $this->cachedCompiledUnicodeArray[$name] = array_map(
                array($this, "handleUnicode"), $this->$name
            );
        }
        return $this->cachedCompiledUnicodeArray[$name];
    }
    
    protected function handleUnicode($num)
    {
        return is_string($num) ? $num : Utils::unicodeToUtf8($num);
    }
}