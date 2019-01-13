<?php

namespace Krixon\Rules\Specification;

/**
 * Requires a value to be falsy.
 *
 * A value is falsy if it is one of:
 *
 *  - Boolean false
 *  - Integer 0
 *  - String '0'
 */
class Falsy implements Specification
{
    public function isSatisfiedBy($value) : bool
    {
        // Note that a loose comparison ($value == false) is not used as it is too loose.

        return false === $value || 0 === $value || '0' === $value;
    }
}