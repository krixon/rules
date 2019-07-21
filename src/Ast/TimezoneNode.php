<?php

namespace Krixon\Rules\Ast;

use DateTimeZone;

/**
 * @method DateTimeZone value()
 */
final class TimezoneNode implements LiteralNode
{
    use ExposesValue;


    public function __construct(DateTimeZone $value)
    {
        $this->value = $value;
    }


    public static function type(): string
    {
        return 'TIMEZONE';
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitTimezone($this);
    }
}
