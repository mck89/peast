<?php
namespace Peast\Syntax\Node;

class ImportNamespaceSpecifier extends ModuleSpecifier
{
    public function compile()
    {
        return "* as " . $this->getLocal()->compile();
    }
}