<?php

namespace Krixon\Rules\Tests\Unit\Exception;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Ast\NumberNode;
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


    public function testProducesExpectedMessageForUnsupportedLogicalOperation()
    {
        $exception = CompilerError::unsupportedLogicalOperation();

        static::assertSame('Unsupported logical operation.', $exception->getMessage());
    }


    public function testProducesExpectedMessageForUnsupportedComparison()
    {
        $comparison = ComparisonNode::greaterThan(new IdentifierNode('foo'), new NumberNode(42));
        $exception  = CompilerError::unsupportedComparisonOperatorFromNode($comparison);

        static::assertSame(
            "Unsupported comparison operator 'GREATER' for identifier 'foo' and operand type 'NUMBER'.",
            $exception->getMessage()
        );
    }
}
