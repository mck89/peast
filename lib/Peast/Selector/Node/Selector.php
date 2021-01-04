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
 * Selector class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Selector
{
    /**
     * Selector groups
     *
     * @var Group[]
     */
    protected $groups = array();

    /**
     * Adds a new group
     *
     * @param Group $group Group
     *
     * @return $this
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
        return $this;
    }

    /**
     * Returns the groups
     *
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }
}