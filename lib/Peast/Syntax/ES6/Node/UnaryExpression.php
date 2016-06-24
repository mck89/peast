<?php
namespace Peast\Syntax\ES6\Node;

class UnaryExpression extends Node implements Expression
{
    protected $operator;
    
    protected $prefix = true;
    
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
}