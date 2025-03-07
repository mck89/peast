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
 * A node that represents a key value pair for an import attribute.
 * For example: return a + 1
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class ImportAttribute extends Node
{
    /**
     * Map of node properties
     * 
     * @var array 
     */
    protected $propertiesMap = array(
        "key" => true,
        "value" => true
    );
    
    /**
     * Import attribute key
     * 
     * @var Identifier|Literal 
     */
    protected $key;
    
    /**
     * Import attribute value
     * 
     * @var Literal 
     */
    protected $value;
    
    /**
     * Returns the import attribute key
     * 
     * @return Identifier|Literal 
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * Sets the import attribute key
     * 
     * @param Identifier|Literal $key The import attribute key
     * 
     * @return $this
     */
    public function setKey($key)
    {
        $this->assertType($key, array("Identifier", "Literal"));
        $this->key = $key;
        return $this;
    }
    
    /**
     * Returns the import attribute value
     * 
     * @return Literal 
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Sets the import attribute value
     * 
     * @param Literal $value The import attribute value
     * 
     * @return $this
     */
    public function setValue(Literal $value)
    {
        $this->value = $value;
        return $this;
    }
}