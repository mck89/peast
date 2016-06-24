<?php
namespace Peast\Syntax\ES6\Node;

class ImportSpecifier extends ModuleSpecifier
{
    protected $imported;
    
    public function getImported()
    {
        return $this->imported;
    }
    
    public function setImported(Identifier $imported)
    {
        $this->imported = $imported;
        return $this;
    }
}