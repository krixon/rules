<?php

namespace Krixon\Rules\Ast;

class StringNode implements LiteralNode
{
    private $value;


    public function __construct(string $value)
    {
        $this->value = $value;
    }


    public function value()
    {
        return $this->value;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitString($this);
    }
}
