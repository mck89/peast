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
 * Selector part index pseudo class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class PseudoIndex extends Pseudo
{
    /**
     * Priority
     *
     * @var int
     */
    protected $priority = 2;

    /**
     * Step
     *
     * @var int
     */
    protected $step = 0;

    /**
     * Offset
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Sets the step
     *
     * @param int $step Step
     *
     * @return $this
     */
    public function setStep($step)
    {
        $this->step = $step;
        return $this;
    }

    /**
     * Returns the step
     *
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Sets the offset
     *
     * @param int $offset Offset
     *
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Returns the offset
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
}