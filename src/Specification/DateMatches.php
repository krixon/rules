<?php

namespace Krixon\Rules\Specification;

use DateTimeInterface;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;

class DateMatches implements Specification
{
    private $date;
    private $operator;


    public function __construct(DateTimeInterface $date, ?Operator $operator = null)
    {
        $operator = $operator ?? Operator::equals();

        if (!$this->supportsOperator($operator)) {
            throw new UnsupportedOperator($this, $operator);
        }

        $this->date     = $date;
        $this->operator = $operator;
    }


    public function isSatisfiedBy($value) : bool
    {
        $value = $this->extract($value);

        switch (true) {
            case $this->operator->isEquals():
                return $value == $this->date;
            case $this->operator->isLessThan():
                return $value < $this->date;
            case $this->operator->isLessThanOrEqualTo():
                return $value <= $this->date;
            case $this->operator->isGreaterThan():
                return $value > $this->date;
            case $this->operator->isGreaterThanOrEqualTo():
                return $value >= $this->date;
        }

        // @codeCoverageIgnoreStart
        // Already validated in the constructor that the operator is supported. This line cannot be reached
        // in a bug-free implementation.
        throw new UnsupportedOperator($this, $this->operator);
        // @codeCoverageIgnoreEnd
    }


    protected function supportsOperator(Operator $operator) : bool
    {
        return $operator->isEquals()
            || $operator->isLessThan()
            || $operator->isLessThanOrEqualTo()
            || $operator->isGreaterThan()
            || $operator->isGreaterThanOrEqualTo();
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
    protected function extract($value) : DateTimeInterface
    {
        if (!$value instanceof DateTimeInterface) {
            throw new UnsupportedValue($this, $value, DateTimeInterface::class);
        }

        return $value;
    }
}
