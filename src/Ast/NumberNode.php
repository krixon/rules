<?php

namespace Krixon\Rules\Ast;

/**
 * @method float value()
 */
final class NumberNode implements LiteralNode
{
    use ExposesValue;


    public function __construct(float $value)
    {
        $this->value = $value;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitNumber($this);
    }
}
