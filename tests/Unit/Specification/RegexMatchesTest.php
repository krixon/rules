<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\RegexMatches;

class RegexMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            [new RegexMatches('/[a-z]+/i'), 'foo', true],
            [new RegexMatches('/[a-z]+/'), 'FOO', false],
        ];
    }


    /**
     * @dataProvider unsatisfiedOnNonStringValueProvider
     *
     * @param mixed $unsupported
     */
    public static function testUnsatisfiedOnNonStringValue($unsupported) : void
    {
        static::assertFalse((new RegexMatches('foo'))->isSatisfiedBy($unsupported));
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
