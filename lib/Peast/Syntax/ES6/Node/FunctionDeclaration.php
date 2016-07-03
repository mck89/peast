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

class FunctionDeclaration extends Node implements Declaration, Function_
{
    use Extension\Function_;
    
    public function setId($id)
    {
        $this->assertType($id, "Identifier");
        return $this->id = $id;
    }
}