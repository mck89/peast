<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES6\Node;

class IfStatement extends Node implements Statement
{
    protected $test;
    
    protected $consequent;
    
    protected $alternate;
    
    public function getTest()
    {
        return $this->test;
    }
    
    public function setTest(Expression $test)
    {
        $this->test = $test;
        return $this;
    }
    
    public function getConsequent()
    {
        return $this->consequent;
    }
    
    public function setConsequent(Statement $consequent)
    {
        $this->consequent = $consequent;
        return $this;
    }
    
    public function getAlternate()
    {
        return $this->alternate;
    }
    
    public function setAlternate($alternate)
    {
        $this->assertType($alternate, "Statement", true);
        $this->alternate = $alternate;
        return $this;
    }
}