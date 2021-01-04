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

use Peast\Selector\Node\Selector;

/**
 * Selector part selector pseudo class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class PseudoSelector extends Pseudo
{
    /**
     * Selector
     *
     * @var Selector
     */
    protected $selector;

    /**
     * Sets the selector
     *
     * @param Selector $selector Selector
     *
     * @return $this
     */
    public function setSelector(Selector $selector)
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * Returns the selector
     *
     * @return Selector
     */
    public function getSelector()
    {
        return $this->selector;
    }
}