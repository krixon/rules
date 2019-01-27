<?php

namespace Krixon\Rules\Tests\Unit\Compiler;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Compiler\BaseCompiler;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function testThrowsWhenNoSpecificationCanBeProduced()
    {
        static::expectException(CompilerError::class);
        static::expectExceptionMessage('Expected to have a specification but none found');

        $this->compiler()->compile(new IdentifierNode('foo'));
    }


    private function compiler() : BaseCompiler
    {
        return new class ($this->createMock(Specification::class)) extends BaseCompiler
        {
            private $specification;


            public function __construct(Specification $specification)
            {
                $this->specification = $specification;
            }


            protected function generate(ComparisonNode $comparison) : Specification
            {
                return $this->specification;
            }
        };
    }
}