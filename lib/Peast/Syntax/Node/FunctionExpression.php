<?php
namespace Peast\Syntax\Node;

class FunctionExpression extends Node implements Expression, Function_
{
    use Extension\Function_;
}