<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\NumberNode;
use Krixon\Rules\Ast\StringNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\StringMatches;
use Krixon\Rules\Specification\StringMatchesGenerator;
use PHPUnit\Framework\TestCase;

class StringMatchesGeneratorTest extends TestCase
{
    public function testGeneratesExpectedSpecification() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('literalValue')->willReturn('Rimmer');
        $comparison->method('operator')->willReturn(Operator::equals());

        $specification = (new StringMatchesGenerator)->attempt($comparison);

        static::assertInstanceOf(StringMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy('Rimmer'));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('literalValue')->willReturn('Rimmer');
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new StringNode('Rimmer'));
        // IN is not valid with a string operand.
        $comparison->method('operator')->willReturn(Operator::in());

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        (new StringMatchesGenerator)->attempt($comparison);
    }


    public function testThrowsWithUnsupportedComparisonValue() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('literalValue')->willReturn(42);
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new NumberNode(42));
        $comparison->method('operator')->willReturn(Operator::equals());

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_VALUE_TYPE);

        (new StringMatchesGenerator)->attempt($comparison);
    }
}
