<?php
namespace Peast\Syntax\Node;

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
    
    public function compile()
    {
        $strings = array();
        foreach ($this->getElements() as $el) {
            if ($el === null) {
                $strings[] = "";
            } else {
                $strings[] = $el->compile();
            }
        }
        
        return "[" . implode(",", $strings) . "]";
    }
}