<?php

namespace Krixon\Rules\Specification;

class NumberMatches implements Specification
{
    private $number;
    private $epsilon;


    public function __construct(float $number, float $epsilon = 0.00001)
    {
        $this->number  = $number;
        $this->epsilon = $epsilon;
    }


    public function isSatisfiedBy($value) : bool
    {
        return is_numeric($value) && abs($this->number - $value) < $this->epsilon;
    }
}
