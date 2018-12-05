<?php

namespace Krixon\Rules\Tests\Functional;

use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Ast\LiteralNode;
use Krixon\Rules\Compiler;
use Krixon\Rules\ExpressionParser;
use Krixon\Rules\Specification\Composite;
use Krixon\Rules\Specification\Not;
use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;

class CompilingTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testCompiles(string $expression, Specification $expected)
    {
        $parser        = new ExpressionParser();
        $ast           = $parser->parse($expression);
        $specification = $this->compiler()->compile($ast);

        static::assertEquals($expected, $specification);
    }


    public function dataProvider()
    {
        return [
            [
                'foo is "bar"',
                $this->identifierMatches('foo', 'bar')
            ],
            [
                'foo not "bar"',
                new Not($this->identifierMatches('foo', 'bar'))
            ],
            [
                'foo in ["a", "b", "c"]',
                Composite::or(
                    $this->identifierMatches('foo', 'a'),
                    $this->identifierMatches('foo', 'b'),
                    $this->identifierMatches('foo', 'c')
                )
            ],
            [
                'foo is "bar" or (deep is 1 and (deeper is 2))',
                Composite::or(
                    $this->identifierMatches('foo', 'bar'),
                    Composite::and(
                        $this->identifierMatches('deep', 1),
                        $this->identifierMatches('deeper', 2)
                    )
                )
            ],
            [
                '(foo is "bar") and (a is 1 or b is 2) and (c is "red" or d is "blue")',
                Composite::and(
                    $this->identifierMatches('foo', 'bar'),
                    Composite::or(
                        $this->identifierMatches('a', 1),
                        $this->identifierMatches('b', 2)
                    ),
                    Composite::or(
                        $this->identifierMatches('c', 'red'),
                        $this->identifierMatches('d', 'blue')
                    )
                )
            ],
            [
                'foo is 1 or foo is 2 or foo is 3 or foo is 4',
                Composite::or(
                    $this->identifierMatches('foo', 1),
                    $this->identifierMatches('foo', 2),
                    $this->identifierMatches('foo', 3),
                    $this->identifierMatches('foo', 4)
                )
            ],
            [
                'foo is 1 or foo is 2 and foo is 3',
                Composite::and(
                    Composite::or(
                        $this->identifierMatches('foo', 1),
                        $this->identifierMatches('foo', 2)
                    ),
                    $this->identifierMatches('foo', 3)
                )
            ],
            [
                '(foo is 1 or foo is 2) and foo is 3',
                Composite::and(
                    Composite::or(
                        $this->identifierMatches('foo', 1),
                        $this->identifierMatches('foo', 2)
                    ),
                    $this->identifierMatches('foo', 3)
                )
            ],
            [
                'foo is 1 or (foo is 2 and foo is 3)',
                Composite::or(
                    $this->identifierMatches('foo', 1),
                    Composite::and(
                        $this->identifierMatches('foo', 2),
                        $this->identifierMatches('foo', 3)
                    )
                )
            ],
        ];
    }


    private function compiler() : Compiler
    {
        $fn = function (string $identifier, $value) : Specification {
            return $this->identifierMatches($identifier, $value);
        };

        return new class ($fn) extends Compiler
        {
            private $fn;


            public function __construct(\Closure $fn)
            {
                $this->fn = $fn;
            }


            protected function literal(IdentifierNode $identifier, LiteralNode $node) : Specification
            {
                $fn = $this->fn;

                return $fn($identifier->fullName(), $node->value());
            }
        };
    }


    private function identifierMatches(string $identifier, $value)
    {
        return new class ($identifier, $value) implements Specification
        {
            private $identifier;
            private $value;


            public function __construct($identifier, $value)
            {
                $this->identifier = $identifier;
                $this->value      = $value;
            }


            public function isSatisfiedBy($value) : bool
            {
                return $this->value === $value;
            }
        };
    }
}
