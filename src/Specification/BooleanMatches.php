<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function is_bool;

class BooleanMatches implements Specification
{
    private $boolean;


    public function __construct(bool $boolean)
    {
        $this->boolean = $boolean;
    }


    public function isSatisfiedBy($value) : bool
    {
        if (!is_bool($value)) {
            // Don't support loose comparison.
            throw new UnsupportedValue($this, $value, 'bool');
        }

        return $value === $this->boolean;
    }
}