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

class ExportSpecifier extends ModuleSpecifier
{
    protected $exported;
    
    public function getExported()
    {
        return $this->exported;
    }
    
    public function setExported(Identifier $exported)
    {
        $this->exported = $exported;
        return $this;
    }
}