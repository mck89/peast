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

class SwitchStatement extends Node implements Statement
{
    protected $discriminant;
    
    protected $cases = array();
    
    public function getDiscriminant()
    {
        return $this->discriminant;
    }
    
    public function setDiscriminant(Expression $discriminant)
    {
        $this->discriminant = $discriminant;
        return $this;
    }
    
    public function getCases()
    {
        return $this->cases;
    }
    
    public function setCases($cases)
    {
        $this->assertArrayOf($cases, "SwitchCase");
        $this->cases = $cases;
        return $this;
    }
}