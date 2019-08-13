<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Ast\ComparisonNode;
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

        $comparison->method('isValueString')->willReturn(true);
        $comparison->method('literalValue')->willReturn('Rimmer');
        $comparison->method('isEquals')->willReturn(true);

        $specification = (new StringMatchesGenerator)->attempt($comparison);

        static::assertInstanceOf(StringMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy('Rimmer'));
    }


    public function testSkipsGenerationWithNonNumberValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueString')->willReturn(false);

        static::assertNull((new StringMatchesGenerator)->attempt($comparison));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueString')->willReturn(true);
        $comparison->method('literalValue')->willReturn('Rimmer');
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new StringNode('Rimmer'));
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        (new StringMatchesGenerator)->attempt($comparison);
    }
}
