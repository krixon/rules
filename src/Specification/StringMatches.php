<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function in_array;
use function is_array;
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

        if (is_array($this->string)) {
            return $this->contains($value);
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
        // This check is overly simplistic on the basis that the value will be checked next via supportsValue().
        // For the purposes of the operator check, we assume we either have an array of strings or a single string.

        if (is_array($value)) {
            return $operator->isIn();
        }

        return $operator->isEquals()
            || $operator->isMatches()
            || $operator->isLessThan()
            || $operator->isLessThanOrEqualTo()
            || $operator->isGreaterThan()
            || $operator->isGreaterThanOrEqualTo();
    }


    protected function supportsValue($value, &$expected) : bool
    {
        $expected = 'string | string[] | regex';

        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $item) {
            if (!is_string($item)) {
                return false;
            }
        }

        return true;
    }


    private function contains(string $value) : bool
    {
        return in_array($value, $this->string, true);
    }
}
