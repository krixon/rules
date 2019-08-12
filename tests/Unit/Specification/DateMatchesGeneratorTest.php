<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use DateTimeImmutable;
use DateTimeInterface;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\DateNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\DateMatches;
use Krixon\Rules\Specification\DateMatchesGenerator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use PHPUnit\Framework\TestCase;

class DateMatchesGeneratorTest extends TestCase
{
    public function testGeneratesExpectedSpecification() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueDate')->willReturn(true);
        $comparison->method('literalValue')->willReturn(new DateTimeImmutable('2000-01-01 00:00:00'));
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $specification = self::generator()->attempt($comparison);

        static::assertInstanceOf(DateMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy(new DateTimeImmutable('2020-01-01 00:00:00')));
    }


    public function testSkipsGenerationWithNonDateValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueDate')->willReturn(false);

        static::assertNull(self::generator()->attempt($comparison));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $date       = new DateTimeImmutable('2000-01-01 00:00:00');
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueDate')->willReturn(true);
        $comparison->method('literalValue')->willReturn($date);
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new DateNode($date));
        $comparison->method('operator')->willReturn(Operator::matches());

        $generator  = $this->getMockForAbstractClass(DateMatchesGenerator::class);

        $generator->method('generate')->willThrowException($this->createMock(UnsupportedOperator::class));

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        $generator->attempt($comparison);
    }


    private static function generator() : DateMatchesGenerator
    {
        return new class() extends DateMatchesGenerator
        {
            protected function generate(DateTimeInterface $date, Operator $operator) : DateMatches
            {
                return new class($date, $operator) extends DateMatches
                {
                    protected function extract($value) : DateTimeInterface
                    {
                        return $value;
                    }
                };
            }
        };
    }
}