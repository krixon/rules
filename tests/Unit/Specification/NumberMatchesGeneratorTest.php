<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\NumberNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
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

        $specification = self::generator()->attempt($comparison);

        static::assertInstanceOf(NumberMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy(43));
    }


    public function testSkipsGenerationWithNonNumberValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueNumber')->willReturn(false);

        static::assertNull(self::generator()->attempt($comparison));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueNumber')->willReturn(true);
        $comparison->method('literalValue')->willReturn(42);
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new NumberNode(42));
        $comparison->method('operator')->willReturn(Operator::matches());

        $generator = $this->getMockForAbstractClass(NumberMatchesGenerator::class);

        $generator->method('generate')->willThrowException($this->createMock(UnsupportedOperator::class));

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        $generator->attempt($comparison);
    }


    private static function generator() : NumberMatchesGenerator
    {
        return new class() extends NumberMatchesGenerator
        {
            protected function generate(float $number, Operator $operator) : NumberMatches
            {
                return new class($number, $operator) extends NumberMatches
                {
                    protected function extract($value)
                    {
                        return $value;
                    }
                };
            }
        };
    }
}
