<?php

namespace Krixon\Rules\Ast;

class NegationNode implements Node
{
    private $negated;


    public function __construct(Node $negated)
    {
        $this->negated = $negated;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitNegation($this);
    }


    public function negated() : Node
    {
        return $this->negated;
    }
}