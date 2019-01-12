<?php

namespace Krixon\Rules\Tests\Unit\Compiler;

use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Ast\LiteralNode;
use Krixon\Rules\Ast\StringNode;
use Krixon\Rules\Compiler\BaseCompiler;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Specification;
use Krixon\Rules\Specification\StringMatches;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function testThrowsWhenExpectedIdentifierNotFound()
    {
        static::expectException(CompilerError::class);
        static::expectExceptionMessage('Expected to have an identifier node but none found');

        $this->compiler()->compile(new StringNode('foo'));
    }


    public function testThrowsWhenNoSpecificationCanBeProduced()
    {
        static::expectException(CompilerError::class);
        static::expectExceptionMessage('Expected to have a specification but none found');

        $this->compiler()->compile(new IdentifierNode('foo'));
    }


    private function compiler() : BaseCompiler
    {
        return new class () extends BaseCompiler
        {
            protected function literal(IdentifierNode $identifier, LiteralNode $node) : Specification
            {
                return new StringMatches('foo');
            }
        };
    }
}