<?php
namespace Peast\Syntax\Node;

class ClassExpression extends Node implements Expression, Class_
{
    use Extension\Class_;
}