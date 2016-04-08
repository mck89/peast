<?php
namespace Peast\Syntax\Node;

class Super extends Node
{
    public function compile()
    {
        return "super";
    }
}