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

/**
 * A node that represents a template literal.
 * For example: `this is a ${test()} template`
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class TemplateLiteral extends Node implements Expression
{
    /**
     * Array of quasis that are the literal parts of the template
     * 
     * @var TemplateElement[] 
     */
    protected $quasis = array();
    
    /**
     * Array of expressions inside the template
     * 
     * @var Expression[] 
     */
    protected $expressions = array();
    
    /**
     * Returns the array of quasis that are the literal parts of the template
     * 
     * @return TemplateElement[] 
     */
    public function getQuasis()
    {
        return $this->quasis;
    }
    
    /**
     * Sets the array of quasis that are the literal parts of the template
     * 
     * @param TemplateElement[] $quasis Quasis
     * 
     * @return $this
     */
    public function setQuasis($quasis)
    {
        $this->assertArrayOf($quasis, "TemplateElement");
        $this->quasis = $quasis;
        return $this;
    }
    
    /**
     * Returns the array of expressions inside the template
     * 
     * @return Expression[]
     */
    public function getExpressions()
    {
        return $this->expressions;
    }
    
    /**
     * Sets the array of expressions inside the template
     * 
     * @param Expression[] $expressions Expressions
     * 
     * @return $this
     */
    public function setExpressions($expressions)
    {
        $this->assertArrayOf($expressions, "Expression");
        $this->expressions = $expressions;
        return $this;
    }
}