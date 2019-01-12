<?php

namespace Krixon\Rules\Ast;

interface LiteralNode extends Node
{
    public function value();
}
