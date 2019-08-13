<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\StringNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\RegexMatches;
use Krixon\Rules\Specification\RegexMatchesGenerator;
use PHPUnit\Framework\TestCase;

class RegexMatchesGeneratorTest extends TestCase
{
    public function testGeneratesExpectedSpecification() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueString')->willReturn(true);
        $comparison->method('literalValue')->willReturn('/bar/');
        $comparison->method('isMatches')->willReturn(true);

        $specification = (new RegexMatchesGenerator)->attempt($comparison);

        static::assertInstanceOf(RegexMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy('foobarbaz'));
    }


    public function testSkipsGenerationWithNonNumberValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueString')->willReturn(false);

        static::assertNull((new RegexMatchesGenerator)->attempt($comparison));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueString')->willReturn(true);
        $comparison->method('literalValue')->willReturn('/foo/');
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new StringNode('/foo/'));
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        (new RegexMatchesGenerator())->attempt($comparison);
    }
}
