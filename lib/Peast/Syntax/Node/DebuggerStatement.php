<?php
namespace Peast\Syntax\Node;

class DebuggerStatement extends Node implements Statement
{
    public function compile()
    {
        return "debugger;";
    }
}