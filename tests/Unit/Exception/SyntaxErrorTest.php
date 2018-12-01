<?php

namespace Krixon\Rules\Tests\Unit\Exception;

use Krixon\Rules\Exception\SyntaxError;
use PHPUnit\Framework\TestCase;

class SyntaxErrorTest extends TestCase
{
    /**
     * @dataProvider expectedMessageProvider
     */
    public function testProducesExpectedMessage(SyntaxError $e, string $expected)
    {
        static::assertSame($expected, $e->getMessage());
    }


    public function expectedMessageProvider()
    {
        return [
            [
                new SyntaxError('Something bad happened.', 'foo = bar', 1, 4),
                "[line 1, column 4]: Something bad happened.\n\n" .
                "\t1 | foo = bar\n" .
                "\t        ^-- here"
            ],
            [
                new SyntaxError('Something bad happened.', 'foo = bar', 23, 4),
                "[line 23, column 4]: Something bad happened.\n\n" .
                "\t23 | foo = bar\n" .
                "\t         ^-- here"
            ]
        ];
    }
}
