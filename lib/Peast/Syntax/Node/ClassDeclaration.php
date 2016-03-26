<?php
namespace Peast\Syntax\Node;

class ClassDeclaration extends Declaration
{
    use Class_;
    
    public function setId($id)
    {
        $this->assertType($id, "Identifier");
        return parent::setId($id);
    }
}