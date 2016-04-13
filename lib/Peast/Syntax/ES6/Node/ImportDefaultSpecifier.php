<?php
namespace Peast\Syntax\ES6\Node;

class ImportDefaultSpecifier extends Node implements ModuleSpecifier
{
    public function compile()
    {
        return $this->getLocal()->compile();
    }
}