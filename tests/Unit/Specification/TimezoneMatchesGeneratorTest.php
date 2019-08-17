<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use DateTimeZone;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\NumberNode;
use Krixon\Rules\Ast\TimezoneNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\TimezoneMatches;
use Krixon\Rules\Specification\TimezoneMatchesGenerator;
use PHPUnit\Framework\TestCase;

class TimezoneMatchesGeneratorTest extends TestCase
{
    public function testGeneratesExpectedSpecification() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('literalValue')->willReturn(new DateTimeZone('UTC'));
        $comparison->method('operator')->willReturn(Operator::equals());

        $specification = (new TimezoneMatchesGenerator())->attempt($comparison);

        static::assertInstanceOf(TimezoneMatches::class, $specification);
        static::assertTrue($specification->isSatisfiedBy(new DateTimeZone('UTC')));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('literalValue')->willReturn(new DateTimeZone('UTC'));
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new TimezoneNode(new DateTimeZone('UTC')));
        // IN is not valid with a string operand.
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        (new TimezoneMatchesGenerator)->attempt($comparison);
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

        (new TimezoneMatchesGenerator)->attempt($comparison);
    }
}
