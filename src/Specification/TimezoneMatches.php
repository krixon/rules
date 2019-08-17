<?php

namespace Krixon\Rules\Specification;

use DateTimeZone;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function array_map;
use function is_array;
use function is_string;

class TimezoneMatches implements Specification
{
    private $timezone;
    private $operator;


    /**
     * @param DateTimeZone|DateTimeZone[]|string $timezone A timezone, array of timezones or regex pattern string.
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

        if (is_array($timezone)) {
            $timezone = array_map(
                static function (DateTimeZone $timezone) : string {
                    return $timezone->getName();
                },
                $timezone
            );
        } elseif ($timezone instanceof DateTimeZone) {
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
        // This check is overly simplistic on the basis that the value will be checked next via supportsValue().
        // For the purposes of the operator check, we assume we either have an array of timezones or a single timezone.

        if (is_array($value)) {
            return $operator->isIn();
        }

        return $operator->isEquals() || $operator->isMatches();
    }


    protected function supportsValue($value, &$expected) : bool
    {
        $expected = sprintf('%1$s | %1$s[] | regex pattern', DateTimeZone::class);

        if (is_string($value)) {
            return true;
        }

        if (is_array($value)) {
            $expected .= '[]';
        } else {
            $value = [$value];
        }

        foreach ($value as $item) {
            if (!$item instanceof DateTimeZone) {
                return false;
            }
        }

        return true;
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
