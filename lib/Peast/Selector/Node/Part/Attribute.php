<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Selector\Node\Part;

/**
 * Selector part attribute class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Attribute extends Part
{
    /**
     * Priority
     *
     * @var int
     */
    protected $priority = 4;

    /**
     * Attribute names
     *
     * @var array
     */
    protected $names = array();

    /**
     * Attribute match operator
     *
     * @var array
     */
    protected $operator = null;

    /**
     * Attribute value
     *
     * @var mixed
     */
    protected $value = null;

    /**
     * Case insensitive flag
     *
     * @var bool
     */
    protected $caseInsensitive = false;

    /**
     * Regex flag
     *
     * @var bool
     */
    protected $regex = false;

    /**
     * Adds a name
     *
     * @param string $name Name
     *
     * @return $this
     */
    public function addName($name)
    {
        $this->names[] = $name;
        return $this;
    }

    /**
     * Returns the name
     *
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * Sets the operator
     *
     * @param string $operator Operator
     *
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
     * @return array|null
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Sets the value
     *
     * @param mixed $value Value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Returns the value
     *
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the case insensitive flag
     *
     * @param bool $caseInsensitive Case insensitive flag
     *
     * @return $this
     */
    public function setCaseInsensitive($caseInsensitive)
    {
        $this->caseInsensitive = $caseInsensitive;
        return $this;
    }

    /**
     * Returns the case insensitive flag
     *
     * @return bool
     */
    public function getCaseInsensitive()
    {
        return $this->caseInsensitive;
    }

    /**
     * Sets the regex flag
     *
     * @param bool $regex Regex flag
     *
     * @return $this
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;
        return $this;
    }

    /**
     * Returns the regex flag
     *
     * @return bool
     */
    public function getRegex()
    {
        return $this->regex;
    }
}