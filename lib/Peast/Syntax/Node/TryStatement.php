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
    
    public function setHandler($handler)
    {
        $this->assertType($handler, "CatchClause", true);
        $this->handler = $handler;
        return $this;
    }
    
    public function getFinalizer()
    {
        return $this->finalizer;
    }
    
    public function setFinalizer($finalizer)
    {
        $this->assertType($finalizer, "BlockStatement", true);
        $this->finalizer = $finalizer;
        return $this;
    }
    
    public function compile()
    {
        $source = "try " . $this->getBlock()->compile();
        
        if ($handler = $this->getHandler()) {
            $source .= $handler->compile(); 
        }
        
        if ($finalizer = $this->getFinalizer()) {
            $source .= $finalizer->compile(); 
        }
        
        return $source;
    }
}