<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\RegexMatches;

class RegexMatchesTest extends SpecificationTestCase
{
    public function dataProvider() : array
    {
        return [
            [self::specification('/[a-z]+/i'), 'foo', true],
            [self::specification('/[a-z]+/'), 'FOO', false],
        ];
    }


    private static function specification(string $string) : RegexMatches
    {
        return new class($string) extends RegexMatches
        {
            protected function extract($value) : string
            {
                return $value;
            }
        };
    }
}
