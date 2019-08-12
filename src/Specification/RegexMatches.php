<?php

namespace Krixon\Rules\Specification;

abstract class RegexMatches implements Specification
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


    abstract protected function extract($value) : string;
}