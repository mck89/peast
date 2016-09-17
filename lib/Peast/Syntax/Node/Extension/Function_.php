<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\Node\Extension;

/**
 * Trait for function declarations and expressions.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
trait Function_
{
    /**
     * Properties containing child nodes
     * 
     * @var array 
     */
    protected $children = array("id", "params", "body");
    
    /**
     * Function name
     * 
     * @var Identifier 
     */
    protected $id;
    
    /**
     * Function parameters array
     * 
     * @var Pattern[] 
     */
    protected $params = array();
    
    /**
     * Function body
     * 
     * @var BlockStatement 
     */
    protected $body;
    
    /**
     * Generator flag that is true when the function is a generator
     * 
     * @var bool 
     */
    protected $generator = false;
    
    /**
     * Returns function name
     * 
     * @return Identifier
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Sets function name
     * 
     * @param Identifier $id Function name
     * 
     * @return $this
     */
    public function setId($id)
    {
        $this->assertType($id, "Identifier", true);
        $this->id = $id;
        return $this;
    }
    
    /**
     * Returns function parameters array
     * 
     * @return Pattern[]
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Sets function parameters array
     * 
     * @param Pattern[] $params Function parameters array
     * 
     * @return $this
     */
    public function setParams($params)
    {
        $this->assertArrayOf($params, "Pattern");
        $this->params = $params;
        return $this;
    }
    
    /**
     * Returns function body
     * 
     * @return BlockStatement
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Sets function body
     * 
     * @param BlockStatement $body Function body
     * 
     * @return $this
     */
    public function setBody($body)
    {
        $this->assertType($body, "BlockStatement");
        $this->body = $body;
        return $this;
    }
    
    /**
     * Returns the generator flag that is true when the function is a generator
     * 
     * @return bool
     */
    public function getGenerator()
    {
        return $this->generator;
    }
    
    /**
     * Sets the generator flag that is true when the function is a generator
     * 
     * @param bool $generator Generator flag
     * 
     * @return $this
     */
    public function setGenerator($generator)
    {
        $this->generator = (bool) $generator;
        return $this;
    }
}