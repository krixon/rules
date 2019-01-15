<?php

namespace Krixon\Rules\Ast;

/**
 * @method \DateTimeZone value()
 */
final class TimezoneNode implements LiteralNode
{
    use ExposesValue;


    public function __construct(\DateTimeZone $value)
    {
        $this->value = $value;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitTimezone($this);
    }
}
