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

/**
 * Selector part type class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Type extends Part
{
    /**
     * Selector type
     *
     * @var string
     */
    protected $type;

    /**
     * Sets the selector type
     *
     * @param string $type Type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
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
    public function check(Node $node, $parent = null)
    {
        return $node->getType() === $this->type;
    }
}