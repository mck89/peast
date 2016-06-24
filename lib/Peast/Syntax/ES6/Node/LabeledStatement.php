<?php
namespace Peast\Syntax\ES6\Node;

class LabeledStatement extends Node implements Statement
{
    protected $label;
    
    protected $body;
    
    public function getLabel()
    {
        return $this->label;
    }
    
    public function setLabel(Identifier $label)
    {
        $this->label = $label;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(Statement $body)
    {
        $this->body = $body;
        return $this;
    }
}