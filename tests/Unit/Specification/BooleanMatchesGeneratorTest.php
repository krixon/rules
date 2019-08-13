<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Ast\BooleanNode;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\BooleanMatches;
use Krixon\Rules\Specification\BooleanMatchesGenerator;
use PHPUnit\Framework\TestCase;

class BooleanMatchesGeneratorTest extends TestCase
{
    public function testGeneratesExpectedSpecification() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueBoolean')->willReturn(true);
        $comparison->method('literalValue')->willReturn(true);
        $comparison->method('isEquals')->willReturn(true);

        $specification = (new BooleanMatchesGenerator)->attempt($comparison);

        static::assertInstanceOf(BooleanMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy(true));
    }


    public function testSkipsGenerationWithNonNumberValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueBoolean')->willReturn(false);

        static::assertNull((new BooleanMatchesGenerator)->attempt($comparison));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueBoolean')->willReturn(true);
        $comparison->method('literalValue')->willReturn(true);
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new BooleanNode(true));
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        (new BooleanMatchesGenerator)->attempt($comparison);
    }
}
