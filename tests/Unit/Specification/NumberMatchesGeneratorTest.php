<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\NumberNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\NumberMatches;
use Krixon\Rules\Specification\NumberMatchesGenerator;
use PHPUnit\Framework\TestCase;

class NumberMatchesGeneratorTest extends TestCase
{
    public function testGeneratesExpectedSpecification() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueNumber')->willReturn(true);
        $comparison->method('literalValue')->willReturn(42);
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $specification = (new NumberMatchesGenerator())->attempt($comparison);

        static::assertInstanceOf(NumberMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy(43));
    }


    public function testSkipsGenerationWithNonNumberValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueNumber')->willReturn(false);

        static::assertNull((new NumberMatchesGenerator())->attempt($comparison));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueNumber')->willReturn(true);
        $comparison->method('literalValue')->willReturn(42);
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new NumberNode(42));
        $comparison->method('operator')->willReturn(Operator::matches());

        $generator = new NumberMatchesGenerator();

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        $generator->attempt($comparison);
    }
}
