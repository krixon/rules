<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use Krixon\Rules\Specification\StringMatches;
use stdClass;

class StringMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            'foo is foo (implicit operator)' => [new StringMatches('foo'), 'foo', true],
            'bar is foo (implicit operator)' => [new StringMatches('foo'), 'bar', false],

            'foo is foo' => [new StringMatches('foo', Operator::equals()), 'foo', true],
            'bar is foo' => [new StringMatches('foo', Operator::equals()), 'bar', false],

            'b > a'    => [new StringMatches('a', Operator::greaterThan()), 'b', true],
            'a > b'    => [new StringMatches('b', Operator::greaterThan()), 'a', false],
            'ab > abc' => [new StringMatches('abc', Operator::greaterThan()), 'ab', false],
            'zz > aa'  => [new StringMatches('aa', Operator::greaterThan()), 'zz', true],

            'b >= a' => [new StringMatches('a', Operator::greaterThanOrEquals()), 'b', true],
            'a >= a' => [new StringMatches('a', Operator::greaterThanOrEquals()), 'a', true],
            'a >= b' => [new StringMatches('b', Operator::greaterThanOrEquals()), 'a', false],

            'b < a'    => [new StringMatches('a', Operator::lessThan()), 'b', false],
            'a < b'    => [new StringMatches('b', Operator::lessThan()), 'a', true],
            'ab < abc' => [new StringMatches('abc', Operator::lessThan()), 'ab', true],
            'zz < aa'  => [new StringMatches('aa', Operator::lessThan()), 'zz', false],

            'b <= a' => [new StringMatches('a', Operator::lessThanOrEquals()), 'b', false],
            'a <= a' => [new StringMatches('a', Operator::lessThanOrEquals()), 'a', true],
            'a <= b' => [new StringMatches('b', Operator::lessThanOrEquals()), 'a', true],

            'foobar matches /^foo/' => [new StringMatches('/^foo/', Operator::matches()), 'foobar', true],
            'foobar matches /^bar/' => [new StringMatches('/^bar/', Operator::matches()), 'foobar', false],

            'a in [a,b,c]' => [new StringMatches(['a', 'b', 'c'], Operator::in()), 'a', true],
            'b in [a,b,c]' => [new StringMatches(['a', 'b', 'c'], Operator::in()), 'b', true],
            'c in [a,b,c]' => [new StringMatches(['a', 'b', 'c'], Operator::in()), 'c', true],
            'd in [a,b,c]' => [new StringMatches(['a', 'b', 'c'], Operator::in()), 'd', false],
            'A in [a,b,c]' => [new StringMatches(['a', 'b', 'c'], Operator::in()), 'A', false],
        ];
    }


    /**
     * @dataProvider unsupportedOperatorProvider
     *
     * @param string|string[] $value
     */
    public function testThrowsOnUnsupportedOperator(Operator $unsupported, $value) : void
    {
        $this->expectException(UnsupportedOperator::class);

        new StringMatches($value, $unsupported);
    }


    public function unsupportedOperatorProvider() : array
    {
        return [
            // Not supported for non-list literals.
            [Operator::in(), 'foo'],
            // Not supported for list literals.
            [Operator::equals(), ['array', 'of', 'strings']],
            [Operator::matches(), ['array', 'of', 'strings']],
            [Operator::lessThan(), ['array', 'of', 'strings']],
            [Operator::lessThanOrEquals(), ['array', 'of', 'strings']],
            [Operator::greaterThan(), ['array', 'of', 'strings']],
            [Operator::greaterThanOrEquals(), ['array', 'of', 'strings']],
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

        new StringMatches($unsupported, $operator);
    }


    public function unsupportedValueProvider() : array
    {
        return [
            [[42], Operator::in()],
            [[false], Operator::in()],
            [[new stdClass()], Operator::in()],
            [[[]], Operator::in()],
            [[['not valid because in nested array']], Operator::in()],
            [['valid', 'also valid', 'but this number is not ->', 42], Operator::in()],

            [42, Operator::equals()],
            [42.5, Operator::equals()],
            [true, Operator::equals()],
            [false, Operator::equals()],
            [new stdClass(), Operator::equals()],
        ];
    }


    /**
     * @dataProvider unsatisfiedOnNonStringCandidateProvider
     *
     * @param mixed $nonString
     */
    public static function testUnsatisfiedOnNonStringCandidate($nonString) : void
    {
        static::assertFalse((new StringMatches('foo'))->isSatisfiedBy($nonString));
    }


    public static function unsatisfiedOnNonStringCandidateProvider() : array
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
