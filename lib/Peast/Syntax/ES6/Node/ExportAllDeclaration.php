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

class ExportAllDeclaration extends Node implements ModuleDeclaration
{
    protected $source;
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource(Literal $source)
    {
        $this->source = $source;
        return $this;
    }
}