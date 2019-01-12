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
                new SyntaxError('Something bad happened.', "~", 0),
                "[line 1, column 1]: Something bad happened.\n\n" .
                "\t1 | ~\n" .
                "\t    ^-- here"
            ],
            [
                new SyntaxError('Something bad happened.', 'foo = bar', 4),
                "[line 1, column 5]: Something bad happened.\n\n" .
                "\t1 | foo = bar\n" .
                "\t        ^-- here"
            ],
            [
                new SyntaxError('Something bad happened.', "foo = 42\nor\nfoo = 43", 16),
                "[line 3, column 5]: Something bad happened.\n\n" .
                "\t3 | foo = 43\n" .
                "\t        ^-- here"
            ],
            [
                new SyntaxError('Something bad happened.', "a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk", 20),
                "[line 11, column 1]: Something bad happened.\n\n" .
                "\t11 | k\n" .
                "\t     ^-- here"
            ],
        ];
    }


    public function testExposesContext()
    {
        $e = new SyntaxError('message', 'context', 1);

        static::assertSame('context', $e->context());
    }
}
