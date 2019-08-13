<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function is_string;

class StringMatches implements Specification
{
    private $string;


    public function __construct(string $string)
    {
        $this->string = $string;
    }


    public function isSatisfiedBy($value) : bool
    {
        return $this->string === $this->extract($value);
    }


    /**
     * Extract the value to test from the input passed to isSatisfiedBy().
     *
     * By default, this returns the input itself assuming it is of the correct type. This can be overridden to perform
     * custom extraction logic if the input is not the correct type.
     *
     * @param mixed $value
     *
     * @throws UnsupportedValue
     */
    protected function extract($value) : ?string
    {
        return is_string($value) ? $value : null;
    }
}
