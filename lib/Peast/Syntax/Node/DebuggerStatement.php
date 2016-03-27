<?php
namespace Peast\Syntax\Node;

class DebuggerStatement extends Statement
{
    public function compile()
    {
        return "debugger;";
    }
}