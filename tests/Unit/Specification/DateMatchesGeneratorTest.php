<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use DateTimeImmutable;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\DateMatchesGenerator;
use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;

class DateMatchesGeneratorTest extends TestCase
{
    public function testConstructable() : void
    {
        $generator = new DateMatchesGenerator();

        static::assertInstanceOf(DateMatchesGenerator::class, $generator);
    }


    public function testGeneratesExpectedSpecification() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueDate')->willReturn(true);
        $comparison->method('literalValue')->willReturn(new DateTimeImmutable('2000-01-01 00:00:00'));
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $specification = (new DateMatchesGenerator())->attempt($comparison);

        static::assertInstanceOf(Specification::class, $specification);
        static::assertTrue($specification->isSatisfiedBy(new DateTimeImmutable('2020-01-01 00:00:00')));
    }


    public function testSkipsGenerationWithNonDateValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueDate')->willReturn(false);

        static::assertNull((new DateMatchesGenerator())->attempt($comparison));
    }


    public function testThrowsWithNonDateTimeValue() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueDate')->willReturn(true);
        $comparison->method('literalValue')->willReturn('not a DateTimeInterface');

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_VALUE_TYPE);

        (new DateMatchesGenerator())->attempt($comparison);
    }
}
