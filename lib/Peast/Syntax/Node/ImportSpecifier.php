<?php
namespace Peast\Syntax\Node;

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
        $local = $this->getLocal()->getSource();
        $imported = $this->getLocal()->getImported();
        return !$imported || $local === $imported ?
               $local :
               $local . " as " . $imported;
    }
}