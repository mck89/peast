<?php
namespace Peast\Syntax\Node;

class ExportSpecifier extends ModuleSpecifier
{
    protected $exported;
    
    public function getExported()
    {
        return $this->exported;
    }
    
    public function setExported(Identifier $exported)
    {
        $this->exported = $exported;
        return $this;
    }
    
    public function compile()
    {
        $local = $this->getLocal()->compile();
        $exported = $this->getExported()->compile();
        return !$exported || $local === $exported ?
               $local :
               $local . " as " . $exported;
    }
}