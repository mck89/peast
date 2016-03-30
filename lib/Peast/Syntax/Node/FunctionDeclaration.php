<?php
namespace Peast\Syntax\Node;

class FunctionDeclaration extends Node implements Declaration
{
    use Extension\Function_;
    
    public function setId($id)
    {
        $this->assertType($id, "Identifier");
        return parent::setId($id);
    }
}