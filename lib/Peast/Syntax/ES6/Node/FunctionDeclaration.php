<?php
namespace Peast\Syntax\ES6\Node;

class FunctionDeclaration extends Node implements Declaration, Function_
{
    use Extension\Function_;
    
    public function setId($id)
    {
        $this->assertType($id, "Identifier");
        return parent::setId($id);
    }
}