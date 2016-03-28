<?php
namespace Peast\Syntax\Node;

class ClassDeclaration extends Node implements Declaration
{
    use Trait\Class_;
    
    public function setId($id)
    {
        $this->assertType($id, "Identifier");
        return parent::setId($id);
    }
}