<?php

namespace Krixon\Rules\Ast;

interface Node
{
    public function accept(Visitor $visitor) : void;
}
