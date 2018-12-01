<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\StringMatches;

class StringMatchesTest extends SpecificationTestCase
{
    public function testConstructable()
    {
        $specification = new StringMatches('foo');

        static::assertInstanceOf(StringMatches::class, $specification);
    }


    public function dataProvider() : array
    {
        return [
            [new StringMatches('foo'), 'foo', true],
            [new StringMatches('foo'), 'bar', false],
        ];
    }
}
