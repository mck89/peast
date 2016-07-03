<?php
/**
 * This file is part of the REBuilder package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES6\Node;

class ObjectExpression extends Node implements Expression
{
    protected $properties = array();
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    public function setProperties($properties)
    {
        $this->assertArrayOf($properties, "Property");
        $this->properties = $properties;
        return $this;
    }
}