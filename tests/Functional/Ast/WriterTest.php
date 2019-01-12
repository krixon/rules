<?php

namespace Krixon\Rules\Tests\Functional\Ast;

use Krixon\Rules\Ast\Writer;
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
     * @throws \Krixon\Rules\Exception\SyntaxError
     */
    public function testProducesExpectedString(string $expression, string $expected = null) : void
    {
        if (null === $expected) {
            $expected = $expression;
        }

        $ast = (new DefaultParser())->parse($expression);

        static::assertSame($expected, (new Writer())->write($ast));
    }


    public function expectedStringProvider() : array
    {
        return [
            ['foo is 42'],
            ['foo is "bar"'],
            ['foo not "bar"'],
            ['foo in ["bar", "baz"]'],
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
        ];
    }
}