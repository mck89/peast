<?php
namespace Peast\Syntax\ES6\Node;

class ImportSpecifier extends Node implements ModuleSpecifier
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
    
    public function compile()
    {
        $local = $this->getLocal()->compile();
        $imported = $this->getLocal()->getImported();
        return !$imported || $local === $imported ?
               $local :
               $local . " as " . $imported;
    }
}