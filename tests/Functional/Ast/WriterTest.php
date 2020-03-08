<?php

namespace Krixon\Rules\Tests\Functional\Ast;

use Krixon\Rules\Ast\Writer;
use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Parser\DefaultParser;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    /**
     * @dataProvider expectedStringProvider
     *
     * @param string $expression
     * @param string $expected
     *
     * @throws SyntaxError
     */
    public function testProducesExpectedString(string $expression, string $expected = null) : void
    {
        if (null === $expected) {
            $expected = $expression;
        }

        $ast = (new DefaultParser())->parse($expression);

        static::assertSame($expected, (new Writer())->write($ast));
    }


    public static function expectedStringProvider() : array
    {
        return [
            ['foo is 42'],
            ['foo is "bar"'],
            ['foo is true'],
            ['foo is false'],
            ['foo not "bar"'],
            ['foo > 42'],
            ['foo >= 42'],
            ['foo < 42'],
            ['foo <= 42'],
            ['foo matches "bar"'],
            ['foo not > 42'],
            ['foo not matches "bar"'],
            ['foo in ["bar", "baz"]'],
            ['foo between 10 and 20'],
            [
                'foo is date:"2000-01-01 12:30:45"',
                'foo is date:"2000-01-01T12:30:45+00:00"',
            ],
            [
                'foo is date:"2000-01-01 12:30:45" in "Asia/Tokyo"',
                'foo is date:"2000-01-01T12:30:45+09:00" in "Asia/Tokyo"',
            ],
            ['foo is timezone:"Asia/Tokyo"'],
            [
                'foo is 42 and bar is 666',
                '(foo is 42 and bar is 666)',
            ],
            [
                '(foo is 42 and bar is 666) or bar is 667',
                '((foo is 42 and bar is 666) or bar is 667)',
            ],
            [
                'foo is 42 and (bar is 666 or bar is 667)',
                '(foo is 42 and (bar is 666 or bar is 667))',
            ],
            ['(foo is 10 xor bar is 10)'],
            [
                'foo contains "bar"',
                'foo contains any of ["bar"]',
            ],
            [
                'foo contains "bar"',
                'foo contains any of ["bar"]',
            ],
            [
                'foo contains ["bar"]',
                'foo contains any of ["bar"]',
            ],
            [
                'foo contains ["bar", "baz"]',
                'foo contains any of ["bar", "baz"]',
            ],
            [
                'foo contains any ["bar", "baz"]',
                'foo contains any of ["bar", "baz"]',
            ],
            [
                'foo contains any of ["bar", "baz"]',
            ],
            [
                'foo contains all ["bar", "baz"]',
                'foo contains all of ["bar", "baz"]',
            ],
            [
                'foo contains all of ["bar", "baz"]',
            ],
        ];
    }
}
