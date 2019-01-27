<?php

namespace Krixon\Rules\Tests\Unit\Compiler;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Ast\LogicalNode;
use Krixon\Rules\Ast\StringNode;
use Krixon\Rules\Compiler\DelegatingCompiler;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;

class DelegatingCompilerTest extends TestCase
{
    public function testThrowsWhenNoSpecificationGeneratorsRegistered()
    {
        static::expectException(CompilerError::class);
        static::expectExceptionMessage('No generator was able to produce a Specification from AST');

        (new DelegatingCompiler())->compile(ComparisonNode::equals(new IdentifierNode('foo'), new StringNode('bar')));
    }


    public function testThrowsWhenNoSpecificationCanBeProduced()
    {
        $generator = $this->createMock(SpecificationGenerator::class);
        $compiler = new DelegatingCompiler($generator);

        $generator->method('attempt')->willReturn(null);

        static::expectException(CompilerError::class);
        static::expectExceptionMessage('No generator was able to produce a Specification from AST');

        $compiler->compile(ComparisonNode::equals(new IdentifierNode('foo'), new StringNode('bar')));
    }


    public function testCanGenerateMultipleSpecifications()
    {
        $specification = $this->createMock(Specification::class);
        $generator     = $this->createMock(SpecificationGenerator::class);
        $compiler      = new DelegatingCompiler($generator);

        $generator
            ->expects($this->exactly(2))
            ->method('attempt')
            ->willReturn($specification);

        $compiler->compile(
            LogicalNode::or(
                ComparisonNode::equals(new IdentifierNode('name'), new StringNode('rimmer')),
                ComparisonNode::equals(new IdentifierNode('name'), new StringNode('lister'))
            )
        );
    }
}