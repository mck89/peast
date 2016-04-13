<?php
namespace Peast\Syntax\ES6\Node;

class ObjectPattern extends Node implements Pattern
{
    protected $properties = array();
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    public function setBody($properties)
    {
        $this->assertArrayOf($properties, "AssignmentProperty");
        $this->properties = $properties;
        return $this;
    }
    
    public function compile()
    {
        return "{" . $this->compileNodeList($this->getProperties(), ",") . "}";
    }
}