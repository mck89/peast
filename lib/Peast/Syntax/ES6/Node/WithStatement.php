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

class WithStatement extends Node implements Statement
{
    protected $object;
    
    protected $body;
    
    public function getObject()
    {
        return $this->object;
    }
    
    public function setObject(Expression $object)
    {
        $this->object = $object;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(Statement $body)
    {
        $this->body = $body;
        return $this;
    }
}