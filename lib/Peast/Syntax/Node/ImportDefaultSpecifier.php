<?php
namespace Peast\Syntax\Node;

class ImportDefaultSpecifier extends Node implements ModuleSpecifier
{
    public function compile()
    {
        return $this->getLocal()->compile();
    }
}