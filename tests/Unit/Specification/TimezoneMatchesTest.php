<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use DateTimeZone;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use Krixon\Rules\Specification\TimezoneMatches;
use stdClass;

class TimezoneMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        $utc    = new DateTimeZone('UTC');
        $tokyo  = new DateTimeZone('Asia/Tokyo');
        $london = new DateTimeZone('Europe/London');

        return [
            [new TimezoneMatches($utc), $utc, true],
            [new TimezoneMatches($utc), $tokyo, false],

            [new TimezoneMatches($utc, Operator::equals()), $utc, true],
            [new TimezoneMatches($utc, Operator::equals()), $tokyo, false],

            [new TimezoneMatches('/^Asia/', Operator::matches()), $tokyo, true],
            [new TimezoneMatches('/^Asia/', Operator::matches()), $utc, false],

            [new TimezoneMatches([$utc, $tokyo], Operator::in()), $utc, true],
            [new TimezoneMatches([$utc, $tokyo], Operator::in()), $tokyo, true],
            [new TimezoneMatches([$utc, $tokyo], Operator::in()), $london, false],
        ];
    }


    /**
     * @dataProvider unsupportedOperatorProvider
     *
     * @param DateTimeZone|DateTimeZone[]|string $value
     */
    public function testThrowsOnUnsupportedOperator(Operator $unsupported, $value) : void
    {
        $this->expectException(UnsupportedOperator::class);

        new TimezoneMatches($value, $unsupported);
    }


    public function unsupportedOperatorProvider() : array
    {
        $utc    = new DateTimeZone('UTC');
        $tokyo  = new DateTimeZone('Asia/Tokyo');
        $london = new DateTimeZone('Europe/London');

        return [
            // Not supported for non-list literals.
            [Operator::in(), $utc],
            [Operator::in(), '/regex/i'],
            // Not supported for list literals.
            [Operator::equals(), [$utc, $tokyo, $london]],
            [Operator::matches(), [$utc, $tokyo, $london]],
            [Operator::lessThan(), [$utc, $tokyo, $london]],
            [Operator::lessThanOrEquals(), [$utc, $tokyo, $london]],
            [Operator::greaterThan(), [$utc, $tokyo, $london]],
            [Operator::greaterThanOrEquals(), [$utc, $tokyo, $london]],
        ];
    }


    /**
     * @dataProvider unsupportedValueProvider
     *
     * @param mixed $unsupported
     */
    public function testThrowsOnUnsupportedValue($unsupported, Operator $operator) : void
    {
        $this->expectException(UnsupportedValue::class);

        new TimezoneMatches($unsupported, $operator);
    }


    public function unsupportedValueProvider() : array
    {
        return [
            [[42], Operator::in()],
            [[false], Operator::in()],
            [[new stdClass()], Operator::in()],
            [[[]], Operator::in()],
            [[[new DateTimeZone('UTC')]], Operator::in()],
            [[new DateTimeZone('UTC'), 42], Operator::in()],

            [42, Operator::equals()],
            [42.5, Operator::equals()],
            [true, Operator::equals()],
            [false, Operator::equals()],
            [new stdClass(), Operator::equals()],
        ];
    }


    /**
     * @dataProvider unsatisfiedOnNonTimezoneCandidateProvider
     *
     * @param mixed $nonTimezone
     */
    public static function testUnsatisfiedOnNonTimezoneCandidate($nonTimezone) : void
    {
        static::assertFalse((new TimezoneMatches(new DateTimeZone('UTC')))->isSatisfiedBy($nonTimezone));
    }


    public static function unsatisfiedOnNonTimezoneCandidateProvider() : array
    {
        return [
            [[]],
            [true],
            [false],
            [null],
            [42],
            [42.5],
            [new \stdClass()],
        ];
    }
}
