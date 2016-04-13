<?php
namespace Peast\Syntax\ES6\Node;

class ImportNamespaceSpecifier extends Node implements ModuleSpecifier
{
    public function compile()
    {
        return "* as " . $this->getLocal()->compile();
    }
}