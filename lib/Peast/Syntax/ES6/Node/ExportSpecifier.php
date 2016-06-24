<?php
namespace Peast\Syntax\ES6\Node;

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
}