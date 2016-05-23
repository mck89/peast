<?php
namespace Peast\Syntax\ES6\Node;

use Peast\Syntax\ES6\Parser;

class RegExpLiteral extends Literal
{
    const KIND_REGEX = "regex";
    
    protected $flags = "";
    
    protected $pattern = "";
    
    protected $kind = self::KIND_REGEX;
    
    public function getPattern()
    {
        return $this->pattern;
    }
    
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
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
    
    public function getRaw()
    {
        return "/" . $this->getPattern() . "/" . $this->getFlags();
    }
    
    public function setRaw($rawValue)
    {
        
        $parts = explode("/", substr($rawValue, 1));
        $flags = array_pop($parts);
        $this->setPattern(implode("/", $parts));
        $this->setFlags($flags);
        return $this;
    }
    
    public function getValue()
    {
        return $this->getRaw();
    }
    
    public function setValue($value)
    {
        return $this->setRaw($value);
    }
}