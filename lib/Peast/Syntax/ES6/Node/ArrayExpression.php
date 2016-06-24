<?php
namespace Peast\Syntax\ES6\Node;

class ArrayExpression extends Node implements Expression
{
    protected $elements = array();
    
    public function getElements()
    {
        return $this->elements;
    }
    
    public function setElements($elements)
    {
        $this->assertArrayOf(
            $elements,
            array("Expression", "SpreadElement"),
            true
        );
        $this->elements = $elements;
        return $this;
    }
}