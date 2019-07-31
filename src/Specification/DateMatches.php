<?php

namespace Krixon\Rules\Specification;

use DateTimeInterface;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;

class DateMatches implements Specification
{
    private $date;
    private $operator;


    public function __construct(DateTimeInterface $date, ?Operator $operator = null)
    {
        $this->date     = $date;
        $this->operator = $operator ?? Operator::equals();

        if (!$this->operator->is(...$this->supportedOperators())) {
            throw new UnsupportedOperator($this, $this->operator);
        }
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


    /**
     * Override this method to extract the date which will be compared against the desired value.
     */
    protected function extract($value) : DateTimeInterface
    {
        return $value;
    }


    /**
     * @return Operator[]
     */
    protected function supportedOperators() : array
    {
        return [
            Operator::equals(),
            Operator::lessThan(),
            Operator::lessThanOrEquals(),
            Operator::greaterThan(),
            Operator::greaterThanOrEquals()
        ];
    }
}
