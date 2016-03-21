<?php
namespace Peast\Syntax\Node;

class TryStatement extends Statement
{
    protected $block;
    
    protected $handler;
    
    protected $finalizer;
    
    public function getBlock()
    {
        return $this->block;
    }
    
    public function setBlock(BlockStatement $block)
    {
        $this->block = $block;
        return $this;
    }
    
    public function getHandler()
    {
        return $this->handler;
    }
    
    public function setHandler(CatchClause $handler)
    {
        $this->handler = $handler;
        return $this;
    }
    
    public function getFinalizer()
    {
        return $this->finalizer;
    }
    
    public function setFinalizer(BlockStatement $finalizer)
    {
        $this->finalizer = $finalizer;
        return $this;
    }
    
    public function getSource()
    {
        $source = "try " . $this->getBlock()->getSource();
        
        if ($handler = $this->getHandler()) {
            $source .= $handler->getSource(); 
        }
        
        if ($finalizer = $this->getFinalizer()) {
            $source .= $finalizer->getSource(); 
        }
        
        return $source;
    }
}