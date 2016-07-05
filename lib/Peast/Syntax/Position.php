<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
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