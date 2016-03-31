<?php
namespace Peast\Syntax\Node;

class ClassDeclaration extends Node implements Declaration, Class_
{
    use Extension\Class_;
    
    public function setId($id)
    {
        $this->assertType($id, "Identifier");
        return parent::setId($id);
    }
}