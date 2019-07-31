<?php

namespace Krixon\Rules\Tests\Functional;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\Compiler;
use Krixon\Rules\Compiler\DelegatingCompiler;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Operator;
use Krixon\Rules\Parser\DefaultParser;
use Krixon\Rules\Parser\Parser;
use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;

abstract class CompilerTestCase extends TestCase implements SpecificationGenerator
{
    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var Parser
     */
    private $parser;


    protected function setUp() : void
    {
        parent::setUp();

        $this->compiler = new DelegatingCompiler($this);
        $this->parser   = new DefaultParser();
    }


    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        return $this->stub($comparison->identifierFullName(), $comparison->operator(), $comparison->literalValue());
    }


    final protected function stub(string $identifier, Operator $operator, $value) : Specification
    {
        return new class ($identifier, $operator, $value) implements Specification
        {
            private $identifier;
            private $comparison;
            private $value;


            public function __construct(string $identifier, string $comparison, $value)
            {
                $this->identifier = $identifier;
                $this->comparison = $comparison;
                $this->value      = $value;
            }


            public function isSatisfiedBy($value) : bool
            {
                return true;
            }
        };
    }


    /**
     * @throws CompilerError
     * @throws SyntaxError
     */
    final protected function compile(string $expression) : ?Specification
    {
        $ast = $this->parser->parse($expression);

        return $this->compiler->compile($ast);
    }
}