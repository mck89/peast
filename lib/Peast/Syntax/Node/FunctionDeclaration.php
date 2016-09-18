<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\Node;

/**
 * A node that represents a function declaration
 * For example: function test () {}
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class FunctionDeclaration extends Node implements Declaration, Function_
{
    use Extension\Function_;
    
    /**
     * Properties containing child nodes
     * 
     * @var array 
     */
    protected $childNodesProps = array("id", "params", "body");
    
    /**
     * Sets the function identifier
     * 
     * @param Identifier $id Function identifier
     * 
     * @return $this
     */
    public function setId($id)
    {
        $this->assertType($id, "Identifier");
        return $this->id = $id;
    }
}