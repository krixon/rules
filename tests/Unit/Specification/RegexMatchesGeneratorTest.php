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

        $specification = self::generator()->attempt($comparison);

        static::assertInstanceOf(RegexMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy('foobarbaz'));
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
        $comparison->method('literalValue')->willReturn('/foo/');
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new StringNode('/foo/'));
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $generator = $this->getMockForAbstractClass(RegexMatchesGenerator::class);

        $generator->method('generate')->willThrowException($this->createMock(UnsupportedOperator::class));

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        $generator->attempt($comparison);
    }


    private static function generator() : RegexMatchesGenerator
    {
        return new class() extends RegexMatchesGenerator
        {
            protected function generate(string $string) : RegexMatches
            {
                return new class($string) extends RegexMatches
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
