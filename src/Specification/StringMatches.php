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
        return $this->string === $value;
    }
}
