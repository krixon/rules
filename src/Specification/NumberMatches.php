<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function abs;
use function is_float;
use const PHP_FLOAT_EPSILON;

class NumberMatches implements Specification
{
    private $number;
    private $operator;
    private $epsilon;


    public function __construct(float $number, ?Operator $operator = null, float $epsilon = PHP_FLOAT_EPSILON)
    {
        $operator = $operator ?? Operator::equals();

        if (!$this->supportsOperator($operator)) {
            throw new UnsupportedOperator($this, $operator);
        }

        $this->number   = $number;
        $this->operator = $operator;
        $this->epsilon  = $epsilon;
    }


    public function isSatisfiedBy($value) : bool
    {
        $value = $this->extract($value);
        $equal = abs($value - $this->number) < $this->epsilon;

        switch (true) {
            case $this->operator->isEquals():
                return $equal;
            case $this->operator->isLessThan():
                return !$equal && ($value < $this->number);
            case $this->operator->isLessThanOrEqualTo():
                return $equal || $value < $this->number;
            case $this->operator->isGreaterThan():
                return !$equal && ($value > $this->number);
            case $this->operator->isGreaterThanOrEqualTo():
                return $equal || $value > $this->number;
        }

        // @codeCoverageIgnoreStart
        // Already validated in the constructor that the operator is supported. This line cannot be reached
        // in a bug-free implementation.
        throw new UnsupportedOperator($this, $this->operator);
        // @codeCoverageIgnoreEnd
    }


    /**
     * Extract the value to test from the input passed to isSatisfiedBy().
     *
     * By default, this returns the input itself assuming it is of the correct type. This can be overridden to perform
     * custom extraction logic if the input is not the correct type.
     *
     * @param mixed $value
     *
     * @return int|float
     * @throws UnsupportedValue
     */
    protected function extract($value)
    {
        if (!is_int($value) && !is_float($value)) {
            throw new UnsupportedValue($this, $value, 'int|float');
        }

        return $value;
    }


    protected function supportsOperator(Operator $operator) : bool
    {
        return $operator->isEquals()
            || $operator->isLessThan()
            || $operator->isLessThanOrEqualTo()
            || $operator->isGreaterThan()
            || $operator->isGreaterThanOrEqualTo();
    }
}
