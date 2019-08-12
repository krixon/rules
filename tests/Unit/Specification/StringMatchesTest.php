<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\StringMatches;

class StringMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            [self::specification('foo'), 'foo', true],
            [self::specification('foo'), 'bar', false],
        ];
    }


    private static function specification(string $string) : StringMatches
    {
        return new class($string) extends StringMatches
        {
            protected function extract($value) : string
            {
                return $value;
            }
        };
    }
}
