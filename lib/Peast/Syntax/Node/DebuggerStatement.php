<?php
namespace Peast\Syntax\Node;

class DebuggerStatement extends Statement
{
    public function getSource()
    {
        return "debugger;";
    }
}