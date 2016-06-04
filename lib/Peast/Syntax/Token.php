<?php
namespace Peast\Syntax;

class Token
{
    const TYPE_BOOLEAN_LITERAL = "BooleanLiteral";
    
    const TYPE_IDENTIFIER = "Identifier";
    
    const TYPE_KEYWORD = "Keyword";
    
    const TYPE_NULL_LITERAL = "NullLiteral";
    
    const TYPE_NUMERIC_LITERAL = "NumericLiteral";
    
    const TYPE_PUNCTUTATOR = "Punctuator";
    
    const TYPE_STRING_LITERAL = "StringLiteral";
    
    const TYPE_REGULAR_EXPRESSION = "RegularExpression";
    
    const TYPE_TEMPLATE = "Template";
    
    protected $type;
    
    protected $source;
    
    protected $location;
    
    public function __construct($type, $source)
    {
        $this->type = $type;
        $this->source = $source;
        $this->location = new SourceLocation();
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function getLocation()
    {
        return $this->location;
    }
    
    public function setStartPosition(Position $position)
    {
        $this->location->setStart($position);
        return $this;
    }
    
    public function setEndPosition(Position $position)
    {
        $this->location->setEnd($position);
        return $this;
    }
}