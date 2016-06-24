<?php
namespace Peast\Syntax\ES6\Node;

class TaggedTemplateExpression extends Node implements Expression
{
    protected $tag;
    
    protected $quasi;
    
    public function getTag()
    {
        return $this->tag;
    }
    
    public function setTag(Expression $tag)
    {
        $this->tag = $tag;
        return $this;
    }
    
    public function getQuasi()
    {
        return $this->quasi;
    }
    
    public function setQuasi(TemplateLiteral $quasi)
    {
        $this->quasi = $quasi;
        return $this;
    }
}