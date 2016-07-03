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

class CallExpression extends Node implements Expression
{
    protected $callee;
    
    protected $arguments = array();
    
    public function getCallee()
    {
        return $this->callee;
    }
    
    public function setCallee($callee)
    {
        $this->assertType($callee, array("Expression", "Super"));
        $this->callee = $callee;
        return $this;
    }
    
    public function getArguments()
    {
        return $this->arguments;
    }
    
    public function setArguments($arguments)
    {
        $this->assertArrayOf($arguments, array("Expression", "SpreadElement"));
        $this->arguments = $arguments;
        return $this;
    }
}