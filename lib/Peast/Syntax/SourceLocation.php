<?php
namespace Peast\Syntax;

class SourceLocation
{
    protected $start;
    
    protected $end;
    
    public function getStart()
    {
        return $this->start;
    }
    
    public function setStart(Position $position)
    {
        $this->start = $position;
        return $this;
    }
    
    public function getEnd()
    {
        return $this->end;
    }
    
    public function setEnd(Position $position)
    {
        $this->end = $position;
        return $this;
    }
}