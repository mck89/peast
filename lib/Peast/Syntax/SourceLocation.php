<?php
namespace Peast\Syntax;

class SourceLocation
{
    protected $source;
    
    protected $start;
    
    protected $end;
    
    public getStart()
    {
        return $this->start;
    }
    
    public setStart(Position $position)
    {
        $this->start = $position;
        return $this;
    }
    
    public getEnd()
    {
        return $this->end;
    }
    
    public setEnd(Position $position)
    {
        $this->end = $position;
        return $this;
    }
}