<?php
namespace Peast\Syntax\Node;

class LabeledStatement extends Statement
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
    
    public function getSource()
    {
        return $this->getLabel()->getSource() .
               ":" .
               $this->getBody()->getSource();
    }
}