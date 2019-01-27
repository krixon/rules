<?php

namespace Krixon\Rules\Specification;

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

        return preg_match($this->pattern, preg_quote($value, $this->delimiter)) === 1;
    }


    /**
     * Override this method to extract the string which will be tested against the pattern.
     */
    protected function extract($value) : string
    {
        return (string)$value;
    }
}