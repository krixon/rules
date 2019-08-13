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
}
