<?php
namespace Peast\Syntax\ES6\Node;

class UpdateExpression extends Node implements Expression
{
    protected $operator;
    
    protected $prefix = false;
    
    protected $argument;
    
    public function getOperator()
    {
        return $this->operator;
    }
    
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }
    
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    public function setPrefix($prefix)
    {
        return $this;
    }
    
    public function getArgument()
    {
        return $this->argument;
    }
    
    public function setArgument(Expression $argument)
    {
        $this->argument = $argument;
        return $this;
    }
    
    public function compile()
    {
        $prefix = $this->getPrefix();
        $ret = "";
        if ($prefix) {
            $ret .= $this->getOperator();
        }
        $ret .= $this->getArgument()->compile();
        if (!$prefix) {
            $ret .= $this->getOperator();
        }
        return $ret;
    }
}