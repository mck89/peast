<?php
namespace Peast\Syntax\ES6\Node;

use Peast\Syntax\ES6\Parser;

class RegExpLiteral extends Literal
{
    const KIND_REGEX = "regex";
    
    protected $flags = "";
    
    protected $kind = self::KIND_REGEX;
    
    public function getPattern()
    {
        return $this->getValue();
    }
    
    public function setPattern($pattern)
    {
        return $this->setValue($pattern);
    }
    
    public function getFlags()
    {
        return $this->flags;
    }
    
    public function setFlags($flags)
    {
        $this->flags = $flags;
        return $this;
    }
    
    public function getRawValue()
    {
        return "/" . $this->getPattern() . "/" . $this->getFlags();
    }
    
    public function setRawValue($rawValue)
    {
        $rawValue = substr($rawValue, 1);
        $parts = preg_split("#/(?=\w+$)#", $rawValue);
        $this->setPattern($parts[0]);
        $this->setPattern($parts[1]);
        return $this;
    }
}