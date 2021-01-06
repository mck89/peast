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
 * Selector pseudo part base class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 *
 * @abstract
 */
abstract class Pseudo extends Part
{
    /**
     * Selector name
     *
     * @var string
     */
    protected $name;

    /**
     * Sets the name
     *
     * @param string $name Name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns an expanded version of the traversable node properties.
     * The return of the function is an array of node properties
     * values with arrays flattened
     *
     * @param Node $node Node
     *
     * @return array
     */
    static protected function getExpandedNodeProperties(Node $node)
    {
        $ret = array();
        $props = Utils::getNodeProperties($node, true);
        foreach ($props as $prop) {
            $val = $node->{$prop["getter"]}();
            if (is_array($val)) {
                $ret = array_merge($ret, $val);
            } else {
                $ret[] = $val;
            }
        }
        return $ret;
    }
}