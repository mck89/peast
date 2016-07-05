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

abstract class ModuleSpecifier extends Node
{
    protected $local;
    
    public function getLocal()
    {
        return $this->local;
    }
    
    public function setLocal(Identifier $local)
    {
        $this->local = $local;
        return $this;
    }
}