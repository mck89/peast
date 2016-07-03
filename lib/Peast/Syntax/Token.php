<?php
/**
 * This file is part of the REBuilder package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax;

class Token
{
    const TYPE_BOOLEAN_LITERAL = "Boolean";
    
    const TYPE_IDENTIFIER = "Identifier";
    
    const TYPE_KEYWORD = "Keyword";
    
    const TYPE_NULL_LITERAL = "Null";
    
    const TYPE_NUMERIC_LITERAL = "Numeric";
    
    const TYPE_PUNCTUTATOR = "Punctuator";
    
    const TYPE_STRING_LITERAL = "String";
    
    const TYPE_REGULAR_EXPRESSION = "RegularExpression";
    
    const TYPE_TEMPLATE = "Template";
    
    protected $type;
    
    protected $value;
    
    protected $location;
    
    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
        $this->location = new SourceLocation();
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getValue()
    {
        return $this->value;
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