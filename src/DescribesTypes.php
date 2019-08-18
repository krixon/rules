<?php

namespace Krixon\Rules;

use DateTimeInterface;
use DateTimeZone;
use function get_class;
use function gettype;
use function is_object;

trait DescribesTypes
{
    private static function describeType($value) : string
    {
        if ($value instanceof DateTimeInterface) {
            return 'date';
        }

        if ($value instanceof DateTimeZone) {
            return 'timezone';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }
}