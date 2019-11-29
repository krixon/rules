<?php

namespace Krixon\Rules\Specification;

use DateTimeZone;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function is_string;

class TimezoneMatches implements Specification
{
    private $timezone;
    private $operator;


    /**
     * @param DateTimeZone|string $timezone A DateTimeZone instance or a regex pattern string.
     */
    public function __construct($timezone, ?Operator $operator = null)
    {
        $operator = $operator ?? Operator::equals();

        if (!$this->supportsOperator($operator, $timezone)) {
            throw new UnsupportedOperator($this, $operator);
        }

        if (!$this->supportsValue($timezone, $expected)) {
            throw new UnsupportedValue($this, $timezone, $expected);
        }

        if ($timezone instanceof DateTimeZone) {
            $timezone = $timezone->getName();
        }

        $this->timezone = $timezone;
        $this->operator = $operator;
    }


    public function isSatisfiedBy($value) : bool
    {
        $value = $this->extract($value);

        if ($value === null) {
            return false;
        }

        return (new StringMatches($this->timezone, $this->operator))->isSatisfiedBy($value->getName());
    }


    protected function supportsOperator(Operator $operator, $value) : bool
    {
        if (is_string($value)) {
            return $operator->isMatches();
        }

        return $operator->isEquals();
    }


    protected function supportsValue($value, &$expected) : bool
    {
        $expected = 'timezone | regex';

        if (is_string($value)) {
            return true;
        }

        return $value instanceof DateTimeZone;
    }


    /**
     * Extract the value to test from the input passed to isSatisfiedBy().
     *
     * By default, this returns the input itself assuming it is of the correct type. This can be overridden to perform
     * custom extraction logic if the input is not the correct type.
     *
     * @param mixed $value
     */
    protected function extract($value) : ?DateTimeZone
    {
        return $value instanceof DateTimeZone ? $value : null;
    }
}
