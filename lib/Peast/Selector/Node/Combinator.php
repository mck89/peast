<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Selector\Node;

/**
 * Selector combinator class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Combinator
{
    /**
     * Operator
     *
     * @var string|null
     */
    protected $operator;

    /**
     * Selector parts
     *
     * @var Part\Part[]
     */
    protected $parts = array();

    /**
     * Sets the operator
     *
     * @param string $operator Operator
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * Returns the operator
     *
     * @return string|null
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Adds a new selector part
     *
     * @param Part\Part $part Part
     * @return $this
     */
    public function addPart(Part\Part $part)
    {
        $this->parts[] = $part;
        return $this;
    }

    /**
     * Returns the selector parts
     *
     * @return Part\Part[]
     */
    public function getParts()
    {
        return $this->parts;
    }
}