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

class CatchClause extends Node
{
    protected $param;
    
    protected $body;
    
    public function getParam()
    {
        return $this->param;
    }
    
    public function setParam(Pattern $param)
    {
        $this->param = $param;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(BlockStatement $body)
    {
        $this->body = $body;
        return $this;
    }
}