<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use DateTimeImmutable;
use DateTimeInterface;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\DateMatches;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;

class DateMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            [self::eq('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:00'), true],
            [self::eq('2000-01-01 00:00:01'), self::date('2000-01-01 00:00:00'), false],
            [self::eq('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:01'), false],

            [self::lt('2000-01-01 00:00:00'), self::date('1999-12-31 23:59:59'), true],
            [self::lt('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:00'), false],
            [self::lt('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:01'), false],

            [self::lte('2000-01-01 00:00:00'), self::date('1999-12-31 23:59:59'), true],
            [self::lte('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:00'), true],
            [self::lte('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:01'), false],

            [self::gt('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:01'), true],
            [self::gt('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:00'), false],
            [self::gt('2000-01-01 00:00:00'), self::date('1999-12-31 23:59:59'), false],

            [self::gte('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:01'), true],
            [self::gte('2000-01-01 00:00:00'), self::date('2000-01-01 00:00:00'), true],
            [self::gte('2000-01-01 00:00:00'), self::date('1999-12-31 23:59:59'), false],
        ];
    }


    /**
     * @dataProvider throwsOnUnsupportedOperatorProvider
     */
    public function testThrowsOnUnsupportedOperator(Operator $unsupported) : void
    {
        $this->expectException(UnsupportedOperator::class);

        new DateMatches(self::date('2000-01-01 00:00:00'), $unsupported);
    }


    public function throwsOnUnsupportedOperatorProvider() : array
    {
        return [
            [Operator::matches()],
            [Operator::in()],
        ];
    }


    private static function eq(string $date) : DateMatches
    {
        return new DateMatches(self::date($date), Operator::equals());
    }


    private static function lt(string $date) : DateMatches
    {
        return new DateMatches(self::date($date), Operator::lessThan());
    }


    private static function lte(string $date) : DateMatches
    {
        return new DateMatches(self::date($date), Operator::lessThanOrEquals());
    }


    private static function gt(string $date) : DateMatches
    {
        return new DateMatches(self::date($date), Operator::greaterThan());
    }


    private static function gte(string $date) : DateMatches
    {
        return new DateMatches(self::date($date), Operator::greaterThanOrEquals());
    }


    private static function date(string $date) : DateTimeImmutable
    {
        return new DateTimeImmutable($date);
    }
}
