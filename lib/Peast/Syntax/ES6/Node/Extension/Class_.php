<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES6\Node\Extension;

/**
 * Trait for class declarations and expressions.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
trait Class_
{
    /**
     * Class name
     * 
     * @var Identifier 
     */
    protected $id;
    
    /**
     * Extended class
     * 
     * @var Expression 
     */
    protected $superClass;
    
    /**
     * Class body
     * 
     * @var ClassBody 
     */
    protected $body;
    
    /**
     * Returns class name
     * 
     * @return Identifier
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Sets class name
     * 
     * @param Identifier $id Class name
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
     * Returns extended class
     * 
     * @return Expression
     */
    public function getSuperClass()
    {
        return $this->superClass;
    }
    
    /**
     * Sets extended class
     * 
     * @param Expression $superClass Extended class
     * 
     * @return $this
     */
    public function setSuperClass($superClass)
    {
        $this->assertType($superClass, "Expression", true);
        $this->superClass = $superClass;
        return $this;
    }
    
    /**
     * Returns class body
     * 
     * @return ClassBody
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Sets class body
     * 
     * @param ClassBody $body Class body
     * 
     * @return $this
     */
    public function setBody($body)
    {
        $this->assertType($body, "ClassBody");
        $this->body = $body;
        return $this;
    }
}