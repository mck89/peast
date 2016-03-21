<?php
namespace Peast\Syntax;

class Position
{
    protected $line;
    
    protected $column;
    
    protected $index;
    
    function __construct($line, $column, $index)
    {
        $this->line = $line;
        $this->column = $column;
        $this->index = $index;
    }
    
    public function getLine()
    {
        return $this->line;
    }
    
    public function getColumn()
    {
        return $this->column;
    }
    
    public function getIndex()
    {
        return $this->index;
    }
}