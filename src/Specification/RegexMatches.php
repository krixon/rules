<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function is_string;

class RegexMatches implements Specification
{
    private $pattern;
    private $delimiter;


    public function __construct(string $pattern)
    {
        $this->pattern   = $pattern;
        $this->delimiter = $pattern[0];
    }


    public function isSatisfiedBy($value) : bool
    {
        $value = $this->extract($value);

        if (null === $value) {
            return false;
        }

        return preg_match($this->pattern, preg_quote($value, $this->delimiter)) === 1;
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