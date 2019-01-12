<?php

namespace Krixon\Rules\Specification;

/**
 * Requires a value to be truthy.
 *
 * A value is truthy if it is one of:
 *
 *  - Boolean true
 *  - Integer 1
 *  - String '1'
 */
class Truthy implements Specification
{
    public function isSatisfiedBy($value) : bool
    {
        // Note that a loose comparison ($value == true) is not used as it is too loose.

        return true === $value || 1 === $value || '1' === $value;
    }
}