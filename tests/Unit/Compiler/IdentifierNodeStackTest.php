<?php

namespace Krixon\Rules\Unit\Compiler;

use Krixon\Rules\Compiler\IdentifierNodeStack;
use Krixon\Rules\Exception\CompilerError;
use PHPUnit\Framework\TestCase;

class IdentifierNodeStackTest extends TestCase
{
    public function testThrowsWhenPoppingEmptyStack()
    {
        static::expectException(CompilerError::class);

        (new IdentifierNodeStack())->pop();
    }
}