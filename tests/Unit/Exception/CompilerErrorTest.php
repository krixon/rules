<?php

namespace Krixon\Rules\Tests\Unit\Exception;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Exception\CompilerError;
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
        $exception = CompilerError::unsupportedComparisonType(ComparisonNode::GREATER, 'foo');

        static::assertSame("Unsupported comparison type 'GREATER' for identifier 'foo'.", $exception->getMessage());
    }
}
