<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use function abs;
use const PHP_FLOAT_EPSILON;

abstract class NumberMatches implements Specification
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
     * @param mixed $value
     * @return int|float
     */
    abstract protected function extract($value);


    protected function supportsOperator(Operator $operator) : bool
    {
        return $operator->isEquals()
            || $operator->isLessThan()
            || $operator->isLessThanOrEqualTo()
            || $operator->isGreaterThan()
            || $operator->isGreaterThanOrEqualTo();
    }
}
