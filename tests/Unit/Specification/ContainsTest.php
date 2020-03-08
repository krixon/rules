<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Contains;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use stdClass;

class ContainsTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            // Specification, Value, Expected to be satisfied.
            [new Contains([]), 'rimmer', false],
            [new Contains(['rimmer']), 'rimmer', true],
            [new Contains(['rimmer']), ['rimmer'], true],
            [new Contains(['rimmer']), [123, 456], false],
            [new Contains([123]), 'rimmer', false],
            [new Contains([123]), ['rimmer', 123], true],
            [new Contains([123], Operator::containsAll()), ['rimmer', 123], true],
            [new Contains([123, 'rimmer'], Operator::containsAll()), ['rimmer', 123], true],
            [new Contains([true]), true, true],
            [new Contains([true]), false, false],
            [new Contains([false]), true, false],
            [new Contains([false]), [true, false], true],
            [new Contains([false]), ['0'], false],
            [new Contains(['0']), false, false],
            [new Contains([true]), ['truthy'], false],
            [new Contains(['truthy']), [true], false],
            [new Contains(['rimmer']), new ArrayIterator(['rimmer']), true],
            [new Contains([new DateTimeImmutable('2000-01-01 00:00:00')]), new DateTimeImmutable('2000-01-01 00:00:00'), true],
            [new Contains([new DateTimeImmutable('2000-01-01 00:00:00')]), new DateTimeImmutable('2000-01-01 00:00:01'), false],
            [new Contains([new DateTimeZone('Europe/London')]), new DateTimeZone('Europe/London'), true],
            [new Contains([new DateTimeZone('Europe/London')]), new DateTimeZone('Asia/Tokyo'), false],
        ];
    }


    /**
     * @dataProvider unsupportedOperatorProvider
     */
    public function testThrowsOnUnsupportedOperator(Operator $unsupported) : void
    {
        $this->expectException(UnsupportedOperator::class);

        new Contains([], $unsupported);
    }


    public static function unsupportedOperatorProvider() : array
    {
        return [
            [Operator::equals()],
            [Operator::greaterThan()],
            [Operator::greaterThanOrEquals()],
            [Operator::lessThan()],
            [Operator::lessThanOrEquals()],
            [Operator::matches()],
            [Operator::in()],
        ];
    }


    /**
     * @dataProvider unsupportedValueProvider
     * @param mixed $unsupported
     */
    public function testThrowsOnUnsupportedValue($unsupported, Operator $operator) : void
    {
        $this->expectException(UnsupportedValue::class);

        new Contains([$unsupported], $operator);
    }


    public static function unsupportedValueProvider() : Generator
    {
        $any = Operator::containsAny();
        $all = Operator::containsAll();

        foreach ([$any, $all] as $operator) {
            yield [[], $operator];
            yield [new stdClass(), $operator];
        }
    }
}
