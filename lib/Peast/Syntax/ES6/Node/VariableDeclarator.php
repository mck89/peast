<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES6\Node;

class VariableDeclarator extends Node
{
    protected $id;
    
    protected $init;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId(Pattern $id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function getInit()
    {
        return $this->init;
    }
    
    public function setInit($init)
    {
        $this->assertType($init, "Expression", true);
        $this->init = $init;
        return $this;
    }
}