<?php
namespace Peast\Syntax\ES6\Node;

class DebuggerStatement extends Node implements Statement
{
    public function compile()
    {
        return "debugger;";
    }
}