<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\StringMatches;

class StringMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            [new StringMatches('foo'), 'foo', true],
            [new StringMatches('foo'), 'bar', false],
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
