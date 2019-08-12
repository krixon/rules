<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\StringNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
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

        $specification = self::generator()->attempt($comparison);

        static::assertInstanceOf(StringMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy('Rimmer'));
    }


    public function testSkipsGenerationWithNonNumberValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueString')->willReturn(false);

        static::assertNull(self::generator()->attempt($comparison));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueString')->willReturn(true);
        $comparison->method('literalValue')->willReturn('Rimmer');
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new StringNode('Rimmer'));
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $generator = $this->getMockForAbstractClass(StringMatchesGenerator::class);

        $generator->method('generate')->willThrowException($this->createMock(UnsupportedOperator::class));

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        $generator->attempt($comparison);
    }


    private static function generator() : StringMatchesGenerator
    {
        return new class() extends StringMatchesGenerator
        {
            protected function generate(string $string) : StringMatches
            {
                return new class($string) extends StringMatches
                {
                    protected function extract($value) : string
                    {
                        return $value;
                    }
                };
            }
        };
    }
}
