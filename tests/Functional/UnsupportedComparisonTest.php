<?php

namespace Krixon\Rules\Tests\Functional;

use Krixon\Rules\Exception\CompilerError;

class UnsupportedComparisonTest extends CompilerTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testCompilerErrorOnUnsupportedComparison(string $expression) : void
    {
        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON);

        $this->compile($expression);
    }


    public static function dataProvider() : array
    {
        return [
            ['foo > true'],
            ['foo > false'],
            ['foo >= true'],
            ['foo >= false'],
            ['foo < true'],
            ['foo < false'],
            ['foo <= true'],
            ['foo <= false'],
        ];
    }
}