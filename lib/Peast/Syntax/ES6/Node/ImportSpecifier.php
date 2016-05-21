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
    
    public function compile()
    {
        $local = $this->getLocal()->compile();
        $imported = $this->getImported()->compile();
        return !$imported || $local === $imported ?
               $local :
               $local . " as " . $imported;
    }
}