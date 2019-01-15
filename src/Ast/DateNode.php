<?php

namespace Krixon\Rules\Ast;

/**
 * @method \DateTimeImmutable value()
 */
final class DateNode implements LiteralNode
{
    use ExposesValue;

    public function __construct(\DateTimeImmutable $value)
    {
        $this->value = $value;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitDate($this);
    }
}
