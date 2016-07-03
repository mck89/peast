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

class YieldExpression extends Node implements Expression
{
    protected $argument;
    
    protected $delegate = false;
    
    public function getArgument()
    {
        return $this->argument;
    }
    
    public function setArgument($argument)
    {
        $this->assertType($argument, "Expression", true);
        $this->argument = $argument;
        return $this;
    }
    
    public function getDelegate()
    {
        return $this->delegate;
    }
    
    public function setDelegate($delegate)
    {
        $this->delegate = (bool) $delegate;
        return $this;
    }
}