<?php
namespace Peast\Syntax\Node;

class EmptyStatement extends Statement
{
    public function compile()
    {
        return ";";
    }
}