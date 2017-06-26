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
 * A node that represents an "export all" declaration.
 * For example: export * from "test"
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class ExportAllDeclaration extends Node implements ModuleDeclaration
{
    /**
     * Map of node properties
     * 
     * @var array 
     */
    protected $propertiesMap = array(
        "source" => true
    );
    
    /**
     * The export source
     * 
     * @var Literal 
     */
    protected $source;
    
    /**
     * Returns the export source
     * 
     * @return Literal
     */
    public function getSource()
    {
        return $this->source;
    }
    
    /**
     * Sets the export source
     * 
     * @param Literal $source Export source
     * 
     * @return $this
     */
    public function setSource(Literal $source)
    {
        $this->source = $source;
        return $this;
    }
}