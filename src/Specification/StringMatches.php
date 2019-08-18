<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function is_string;
use function preg_match;
use function preg_quote;

class StringMatches implements Specification
{
    private $string;
    private $operator;


    /**
     * @param string|string[]
     */
    public function __construct($string, ?Operator $operator = null)
    {
        $operator = $operator ?? Operator::equals();

        if (!$this->supportsOperator($operator, $string)) {
            throw new UnsupportedOperator($this, $operator);
        }

        if (!$this->supportsValue($string, $expected)) {
            throw new UnsupportedValue($this, $string, $expected);
        }

        $this->string   = $string;
        $this->operator = $operator;
    }


    public function isSatisfiedBy($value) : bool
    {
        $value = $this->extract($value);

        if (null === $value) {
            return false;
        }

        switch (true) {
            case $this->operator->isEquals():
                return $this->string === $value;
            case $this->operator->isMatches():
                return preg_match($this->string, preg_quote($value, $this->string[0])) === 1;
            case $this->operator->isLessThan():
                return $value < $this->string;
            case $this->operator->isLessThanOrEqualTo():
                return $value <= $this->string;
            case $this->operator->isGreaterThan():
                return $value > $this->string;
            case $this->operator->isGreaterThanOrEqualTo():
                return $value >= $this->string;
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
     * @throws UnsupportedValue
     */
    protected function extract($value) : ?string
    {
        return is_string($value) ? $value : null;
    }


    protected function supportsOperator(Operator $operator, $value) : bool
    {
        return $operator->isEquals()
            || $operator->isMatches()
            || $operator->isLessThan()
            || $operator->isLessThanOrEqualTo()
            || $operator->isGreaterThan()
            || $operator->isGreaterThanOrEqualTo();
    }


    protected function supportsValue($value, &$expected) : bool
    {
        $expected = 'string | regex';

        return is_string($value);
    }
}
