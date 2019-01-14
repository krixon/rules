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
        $value = $this->extract($value);

        return is_numeric($value) && abs($this->number - $value) < $this->epsilon;
    }


    /**
     * Override this method to extract the number which will be compared against the desired value.
     */
    protected function extract($value)
    {
        return $value;
    }
}
