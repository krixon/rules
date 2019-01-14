<?php

namespace Krixon\Rules\Specification;

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
     * Override this method to extract the string which will be compared against the desired value.
     */
    protected function extract($value) : string
    {
        return $value;
    }
}
