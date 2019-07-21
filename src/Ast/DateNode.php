<?php

namespace Krixon\Rules\Ast;

use DateTimeImmutable;

/**
 * @method DateTimeImmutable value()
 */
final class DateNode implements LiteralNode
{
    use ExposesValue;

    public function __construct(DateTimeImmutable $value)
    {
        $this->value = $value;
    }


    public static function type(): string
    {
        return 'DATE';
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitDate($this);
    }
}
