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
 * Selector group class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Group
{
    /**
     * Selector combinators
     *
     * @var Combinator[]
     */
    protected $combinators = array();

    /**
     * Adds a combinator
     *
     * @param Combinator $combinators Combinator
     *
     * @return $this
     */
    public function addCombinator(Combinator $combinators)
    {
        $this->combinators[] = $combinators;
        return $this;
    }

    /**
     * Returns the combinators
     *
     * @return Combinator[]
     */
    public function getCombinators()
    {
        return $this->combinators;
    }
}