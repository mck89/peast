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

class ExportNamedDeclaration extends Node implements ModuleDeclaration
{
    protected $declaration;
    
    protected $specifiers = array();
    
    protected $source;
    
    public function getDeclaration()
    {
        return $this->declaration;
    }
    
    public function setDeclaration($declaration)
    {
        $this->assertType($declaration, "Declaration", true);
        $this->declaration = $declaration;
        return $this;
    }
    
    public function getSpecifiers()
    {
        return $this->specifiers;
    }
    
    public function setSpecifiers($specifiers)
    {
        $this->assertArrayOf($specifiers, "ExportSpecifier");
        $this->specifiers = $specifiers;
        return $this;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        $this->assertType($source, "Literal", true);
        $this->source = $source;
        return $this;
    }
}