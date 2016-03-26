<?php
namespace Peast\Syntax\Node;

class FunctionDeclaration extends Declaration
{
    use Function_;
    
    public function setId($id)
    {
        $this->assertType($id, "Identifier");
        return parent::setId($id);
    }
}