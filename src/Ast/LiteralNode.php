<?php

namespace Krixon\Rules\Ast;

interface LiteralNode extends Node
{
    public function value();
    public static function type() : string;
}
