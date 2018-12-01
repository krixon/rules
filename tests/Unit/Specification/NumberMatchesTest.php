<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\NumberMatches;

class NumberMatchesTest extends SpecificationTestCase
{
    public function testConstructable()
    {
        $specification = new NumberMatches(42, 0.01);

        static::assertInstanceOf(NumberMatches::class, $specification);
    }


    public function dataProvider() : array
    {
        return [
            [new NumberMatches(42), 42, true],
            [new NumberMatches(42), 43, false],
            [new NumberMatches(42.123), 42.123, true],
            [new NumberMatches(42.55555), 42.55555, true],
            [new NumberMatches(42.55555), 42.55556, false],
            [new NumberMatches(42.5, 0.1), 42.6, false],
            [new NumberMatches(42.5, 0.1), 42.55, true],
            [new NumberMatches(42.5, 0.2), 42.6, true],
            [new NumberMatches(42.55555, 0.0001), 42.55556, true],
            [new NumberMatches(42, 0.00000000001), 42.000000000001, true],
        ];
    }
}
