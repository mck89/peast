<?php
namespace Peast\Syntax\Node;

class ImportNamespaceSpecifier extends Node implements ModuleSpecifier
{
    public function compile()
    {
        return "* as " . $this->getLocal()->compile();
    }
}