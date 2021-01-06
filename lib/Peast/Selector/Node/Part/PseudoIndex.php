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

use Peast\Syntax\Node\Node;
use Peast\Syntax\Utils;

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

    /**
     * Returns true if the selector part matches the given node,
     * false otherwise
     *
     * @param Node $node    Node
     * @param Node $parent  Parent node
     *
     * @return bool
     */
    public function check(Node $node, Node $parent = null)
    {
        $props = Utils::getNodeProperties($parent, true);
        $count = count($props);
        $reverse = $this->name === "nth-last-child";
        if ($reverse) {
            $start = $count - 1 - $this->offset;
            $step = $this->step * -1;
        } else {
            $start = $this->offset;
            $step = $this->step;
        }
        //Step 0 will cause an infinite loop, so it must be set to the
        //number of props so that it will execute only one iteration
        if (!$step) {
            $step = $count;
        }
        for ($i = $start; $i >= 0 && $i < $count; $i += $step) {
            if ($parent->{$props[$i]["getter"]}() === $node) {
                return true;
            }
        }
        return false;
    }
}