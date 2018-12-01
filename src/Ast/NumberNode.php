<?php

namespace Krixon\Rules\Ast;

class NumberNode extends Node implements LiteralNode
{
    private $value;


    public function __construct(float $value)
    {
        $this->value = $value;
    }


    public function value()
    {
        return $this->value;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitNumber($this);
    }
}
