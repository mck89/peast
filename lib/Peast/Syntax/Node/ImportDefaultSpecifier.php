<?php
namespace Peast\Syntax\Node;

class ImportDefaultSpecifier extends ModuleSpecifier
{
    public function compile()
    {
        return $this->getLocal()->compile();
    }
}