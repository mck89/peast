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
 * A node that represents a switch statement.
 * For example: switch (test) {}
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class SwitchStatement extends Node implements Statement
{
    /**
     * Properties containing child nodes
     * 
     * @var array 
     */
    protected $childNodesProps = array("discriminant", "cases");
    
    /**
     * Discriminant expression
     * 
     * @var Expression 
     */
    protected $discriminant;
    
    /**
     * Cases array
     * 
     * @var SwitchCase[] 
     */
    protected $cases = array();
    
    /**
     * Returns the discriminant expression
     * 
     * @return Expression
     */
    public function getDiscriminant()
    {
        return $this->discriminant;
    }
    
    /**
     * Sets the discriminant expression
     * 
     * @param Expression $discriminant Discriminant expression
     * 
     * @return $this
     */
    public function setDiscriminant(Expression $discriminant)
    {
        $this->discriminant = $discriminant;
        return $this;
    }
    
    /**
     * Returns the cases array
     * 
     * @return SwitchCase[]
     */
    public function getCases()
    {
        return $this->cases;
    }
    
    /**
     * Sets the cases array
     * 
     * @param SwitchCase[] $cases Cases array
     * 
     * @return $this
     */
    public function setCases($cases)
    {
        $this->assertArrayOf($cases, "SwitchCase");
        $this->cases = $cases;
        return $this;
    }
}