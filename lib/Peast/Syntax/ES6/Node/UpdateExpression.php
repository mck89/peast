<?php
/**
 * This file is part of the REBuilder package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
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
        $this->prefix = $prefix;
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