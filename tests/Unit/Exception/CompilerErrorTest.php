<?php

namespace Krixon\Rules\Tests\Unit\Exception;

use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use PHPUnit\Framework\TestCase;

class CompilerErrorTest extends TestCase
{
    public function testProducesExpectedMessage()
    {
        $message   = 'Something bad happened.';
        $exception = new CompilerError($message);

        static::assertSame($message, $exception->getMessage());
    }


    public function testProducesExpectedMessageForUnknownIdentifier()
    {
        $exception = CompilerError::unknownIdentifier('foo');

        static::assertSame("Unknown identifier 'foo'.", $exception->getMessage());
    }


    public function testProducesExpectedMessageForUnsupportedComparison()
    {
        $exception = CompilerError::unsupportedComparisonOperator(Operator::greaterThan(), 'foo');

        static::assertSame(
            "Unsupported comparison operator 'GREATER' for identifier 'foo'.",
            $exception->getMessage()
        );
    }
}
