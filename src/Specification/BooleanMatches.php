<?php

namespace Krixon\Rules\Specification;

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
        return $this->extract($value) === $this->boolean;
    }


    /**
     * Extract the value to test from the input passed to isSatisfiedBy().
     *
     * By default, this returns the input itself assuming it is of the correct type. This can be overridden to perform
     * custom extraction logic if the input is not the correct type.
     *
     * @param mixed $value
     */
    protected function extract($value) : ?bool
    {
        return is_bool($value) ? $value : null;
    }
}