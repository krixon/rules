<?php

namespace Krixon\Rules\Specification;

abstract class StringMatches implements Specification
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


    abstract protected function extract($value) : string;
}
