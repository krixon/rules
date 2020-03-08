<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\LiteralNodeList;
use Krixon\Rules\Ast\StringNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Contains;
use Krixon\Rules\Specification\ContainsGenerator;
use PHPUnit\Framework\TestCase;

class ContainsGeneratorTest extends TestCase
{
    public function testGeneratesExpectedSpecification() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueList')->willReturn(true);
        $comparison->method('literalValue')->willReturn(['rimmer']);
        $comparison->method('operator')->willReturn(Operator::containsAny());

        $specification = (new ContainsGenerator())->attempt($comparison);

        static::assertInstanceOf(Contains::class, $specification);
        static::assertTrue($specification->isSatisfiedBy(['rimmer', 'lister']));
    }


    public function testSkipsGenerationWithNonListValueNode() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueList')->willReturn(false);

        static::assertNull((new ContainsGenerator)->attempt($comparison));
    }


    public function testThrowsWithUnsupportedComparisonOperator() : void
    {
        $comparison = $this->createMock(ComparisonNode::class);

        $comparison->method('isValueList')->willReturn(true);
        $comparison->method('literalValue')->willReturn(['rimmer']);
        // This has to use a real node object because static methods cannot be invoked on mocks.
        $comparison->method('value')->willReturn(new LiteralNodeList(new StringNode('rimmer')));
        $comparison->method('operator')->willReturn(Operator::greaterThan());

        $this->expectException(CompilerError::class);
        $this->expectExceptionCode(CompilerError::UNSUPPORTED_COMPARISON_OPERATOR);

        (new ContainsGenerator)->attempt($comparison);
    }
}
