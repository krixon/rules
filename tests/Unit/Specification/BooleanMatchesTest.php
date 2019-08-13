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
}
