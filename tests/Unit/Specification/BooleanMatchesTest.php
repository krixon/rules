<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\BooleanMatches;

class BooleanMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            [new BooleanMatches(true), true, true],
            [new BooleanMatches(false), false, true],
            [new BooleanMatches(true), false, false],
            [new BooleanMatches(false), true, false],
        ];
    }


    /**
     * @dataProvider unsatisfiedOnNonBooleanValueProvider
     *
     * @param mixed $unsupported
     */
    public static function testUnsatisfiedWithNonBooleanValue($unsupported) : void
    {
        static::assertFalse((new BooleanMatches(true))->isSatisfiedBy($unsupported));
    }


    public function unsatisfiedOnNonBooleanValueProvider() : array
    {
        return [
            [[]],
            ['string'],
            [null],
            [42],
            [42.5],
            [new \stdClass()],
        ];
    }
}
