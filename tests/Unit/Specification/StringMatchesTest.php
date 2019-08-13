<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\StringMatches;

class StringMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            [new StringMatches('foo'), 'foo', true],
            [new StringMatches('foo'), 'bar', false],

            [new StringMatches('foo', Operator::equals()), 'foo', true],
            [new StringMatches('foo', Operator::equals()), 'bar', false],

            [new StringMatches('a', Operator::greaterThan()), 'b', false],
            [new StringMatches('b', Operator::greaterThan()), 'a', true],
            [new StringMatches('abc', Operator::greaterThan()), 'ab', true],

            [new StringMatches('a', Operator::greaterThanOrEquals()), 'b', false],
            [new StringMatches('a', Operator::greaterThanOrEquals()), 'a', true],
            [new StringMatches('b', Operator::greaterThanOrEquals()), 'a', true],

            [new StringMatches('a', Operator::lessThan()), 'b', true],
            [new StringMatches('b', Operator::lessThan()), 'a', false],
            [new StringMatches('abc', Operator::lessThan()), 'ab', false],

            [new StringMatches('a', Operator::lessThanOrEquals()), 'b', true],
            [new StringMatches('a', Operator::lessThanOrEquals()), 'a', true],
            [new StringMatches('b', Operator::lessThanOrEquals()), 'a', false],

            [new StringMatches('/^foo/', Operator::matches()), 'foobar', true],
            [new StringMatches('/^bar/', Operator::matches()), 'foobar', false],
        ];
    }


    /**
     * @dataProvider unsupportedOperatorProvider
     */
    public function testThrowsOnUnsupportedOperator(Operator $unsupported) : void
    {
        $this->expectException(UnsupportedOperator::class);

        new StringMatches('foo', $unsupported);
    }


    public function unsupportedOperatorProvider() : array
    {
        return [
            [Operator::in()],
        ];
    }


    /**
     * @dataProvider unsatisfiedOnNonStringValueProvider
     *
     * @param mixed $unsupported
     */
    public static function testUnsatisfiedOnNonStringValue($unsupported) : void
    {
        static::assertFalse((new StringMatches('foo'))->isSatisfiedBy($unsupported));
    }


    public static function unsatisfiedOnNonStringValueProvider() : array
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
