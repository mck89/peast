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

use Peast\Selector\Matches;

/**
 * Nodes query class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Query
{
    /**
     * Current matches
     *
     * @var Selector\Matches
     */
    protected $matches;

    /**
     * Class constructor
     *
     * @param Syntax\Node\Program $root Root node
     */
    public function __construct(Syntax\Node\Program $root)
    {
        $this->matches = new Selector\Matches();
        $this->matches->addMatch($root);
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
        $this->matches = $selector->exec($this->matches);
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
        $this->matches->filter(function ($node, $parent) use ($selector) {
            $newMatch = new Selector\Matches();
            $newMatch->addMatch($node, $parent);
            return $selector->exec($newMatch)->getMatches();
        });
        return $this;
    }
}