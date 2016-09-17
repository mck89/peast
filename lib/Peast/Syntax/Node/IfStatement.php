<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\Node;

/**
 * A node that represents an if statement.
 * For example: if (test) {} else {}
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class IfStatement extends Node implements Statement
{
    /**
     * Properties containing child nodes
     * 
     * @var array 
     */
    protected $children = array("test", "consequent", "alternate");
    
    /**
     * The test expression
     * 
     * @var Expression 
     */
    protected $test;
    
    /**
     * The statement that is activated if the test expression is true
     * 
     * @var Statement 
     */
    protected $consequent;
    
    /**
     * The "else" statement
     * 
     * @var Statement 
     */
    protected $alternate;
    
    /**
     * Returns the test expression
     * 
     * @return Expression
     */
    public function getTest()
    {
        return $this->test;
    }
    
    /**
     * Sets the test expression
     * 
     * @param Expression $test Test expression
     * 
     * @return $this
     */
    public function setTest(Expression $test)
    {
        $this->test = $test;
        return $this;
    }
    
    /**
     * Returns the statement that is activated if the test expression is true
     * 
     * @return Statement
     */
    public function getConsequent()
    {
        return $this->consequent;
    }
    
    /**
     * Sets the statement that is activated if the test expression is true
     * 
     * @param Statement $consequent The consequent expression
     * 
     * @return $this
     */
    public function setConsequent(Statement $consequent)
    {
        $this->consequent = $consequent;
        return $this;
    }
    
    /**
     * Returns the "else" statement
     * 
     * @return Statement
     */
    public function getAlternate()
    {
        return $this->alternate;
    }
    
    /**
     * Sets the "else" statement
     * 
     * @param Statement $alternate The "else" statement
     * 
     * @return $this
     */
    public function setAlternate($alternate)
    {
        $this->assertType($alternate, "Statement", true);
        $this->alternate = $alternate;
        return $this;
    }
}