<?php
namespace Peast\Syntax\Node;

abstract class ModuleSpecifier extends Node
{
    protected $local;
    
    public function getLocal()
    {
        return $this->local;
    }
    
    public function setLocal(Identifier $local)
    {
        $this->local = $local;
        return $this;
    }
}