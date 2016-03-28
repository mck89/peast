<?php
namespace Peast\Syntax\Node;

class EmptyStatement extends Node implements Statement
{
    public function compile()
    {
        return ";";
    }
}