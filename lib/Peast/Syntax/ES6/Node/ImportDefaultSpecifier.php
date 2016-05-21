<?php
namespace Peast\Syntax\ES6\Node;

class ImportDefaultSpecifier extends ModuleSpecifier
{
    public function compile()
    {
        return $this->getLocal()->compile();
    }
}