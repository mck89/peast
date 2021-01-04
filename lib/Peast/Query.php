<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast;

/**
 * Nodes query class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Query
{
    /**
     * Root node
     *
     * @var Syntax\Node\Program
     */
    protected $root;

    /**
     * Class constructor
     *
     * @param Syntax\Node\Program $root Root node
     */
    public function __construct(Syntax\Node\Program $root)
    {
        $this->root = $root;
    }

    /**
     * Finds nodes matching the given selector starting from the
     * current matched nodes, if any, or from the root
     *
     * @param string $selector Selector
     *
     * @return $this
     *
     * @throws Selector\Exception
     */
    public function find($selector)
    {
        $parser = new Selector\Parser($selector);
        $selector = $parser->parse();
        return $this;
    }

    /**
     * Executes the given selector on the current nodes and filters
     * out the nodes which don't match
     *
     * @param string $selector Selector
     *
     * @return $this
     *
     * @throws Selector\Exception
     */
    public function filter($selector)
    {
        $parser = new Selector\Parser($selector);
        $selector = $parser->parse();
        return $this;
    }
}