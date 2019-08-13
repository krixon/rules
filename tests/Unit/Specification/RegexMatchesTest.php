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
}
