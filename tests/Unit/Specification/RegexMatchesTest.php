<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\RegexMatches;

class RegexMatchesTest extends SpecificationTestCase
{
    public function testConstructable()
    {
        $specification = new RegexMatches('/[a-z]+/i');

        static::assertInstanceOf(RegexMatches::class, $specification);
    }


    public function dataProvider() : array
    {
        return [
            [new RegexMatches('/[a-z]+/i'), 'foo', true],
            [new RegexMatches('/[a-z]+/'), 'FOO', false],
        ];
    }
}
