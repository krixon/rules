<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Operator;
use Krixon\Rules\Specification\NumberMatches;
use const PHP_FLOAT_EPSILON;

class NumberMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            [self::eq(42), 42, true],
            [self::eq(42), 43, false],
            [self::eq(42.123), 42.123, true],
            [self::eq(42.55555), 42.55555, true],
            [self::eq(42.55555), 42.55556, false],
            [self::eq(42.5, 0.1), 42.6, false],
            [self::eq(42.5, 0.1), 42.55, true],
            [self::eq(42.5, 0.2), 42.6, true],
            [self::eq(42.55555, 0.0001), 42.55556, true],
            [self::eq(42, 0.00000000001), 42.000000000001, true],

            [self::lt(42), 42, false],
            [self::lt(42), 41, true],
            [self::lt(42.5), 42, true],
            [self::lt(42.00001), 42, true],
            [self::lt(42.0000000000001), 42, true],

            [self::lte(42), 42, true],
            [self::lte(42), 41, true],
            [self::lte(42.5), 42, true],
            [self::lte(42.00001), 42, true],
            [self::lte(42.0000000000001), 42, true],

            [self::gt(42), 42, false],
            [self::gt(42), 41, false],
            [self::gt(42.5), 42, false],
            [self::gt(42.00001), 42, false],
            [self::gt(42.0000000000001), 42, false],

            [self::gte(42), 42, true],
            [self::gte(42), 41, false],
            [self::gte(42.5), 42, false],
            [self::gte(42.00001), 42, false],
            [self::gte(42.0000000000001), 42, false],
        ];
    }


    private static function eq(float $number, float $epsilon = PHP_FLOAT_EPSILON) : NumberMatches
    {
        return new NumberMatches($number, Operator::equals(), $epsilon);
    }


    private static function lt(float $number, float $epsilon = PHP_FLOAT_EPSILON) : NumberMatches
    {
        return new NumberMatches($number, Operator::lessThan(), $epsilon);
    }


    private static function lte(float $number, float $epsilon = PHP_FLOAT_EPSILON) : NumberMatches
    {
        return new NumberMatches($number, Operator::lessThanOrEquals(), $epsilon);
    }


    private static function gt(float $number, float $epsilon = PHP_FLOAT_EPSILON) : NumberMatches
    {
        return new NumberMatches($number, Operator::greaterThan(), $epsilon);
    }


    private static function gte(float $number, float $epsilon = PHP_FLOAT_EPSILON) : NumberMatches
    {
        return new NumberMatches($number, Operator::greaterThanOrEquals(), $epsilon);
    }
}
