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
 * A node that represents the return statement inside functions.
 * For example: return a + 1
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class ReturnStatement extends Node implements Statement
{
    /**
     * Properties containing child nodes
     * 
     * @var array 
     */
    protected $children = array("argument");
    
    /**
     * Optional expression after the return keyword
     * 
     * @var Expression 
     */
    protected $argument;
    
    /**
     * Returns the expression after the return keyword
     * 
     * @return Expression
     */
    public function getArgument()
    {
        return $this->argument;
    }
    
    /**
     * Sets the expression after the return keyword
     * 
     * @param Expression $argument The expression to return
     * 
     * @return $this
     */
    public function setArgument($argument)
    {
        $this->assertType($argument, "Expression", true);
        $this->argument = $argument;
        return $this;
    }
}