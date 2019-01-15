<?php

namespace Krixon\Rules\Tests\Functional;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\DelegatingCompiler;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Parser\DefaultParser;
use Krixon\Rules\Specification\Composite;
use Krixon\Rules\Specification\Not;
use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;

class CompilingTest extends TestCase implements SpecificationGenerator
{
    /**
     * @dataProvider dataProvider
     */
    public function testCompiles(string $expression, Specification $expected)
    {
        $compiler      = new DelegatingCompiler($this);
        $parser        = new DefaultParser();
        $ast           = $parser->parse($expression);
        $specification = $compiler->compile($ast);

        static::assertEquals($expected, $specification);
    }


    public function dataProvider()
    {
        $eq      = 'EQUALS';
        $gt      = 'GREATER';
        $gte     = 'GREATER_EQUALS';
        $lt      = 'LESS';
        $lte     = 'LESS_EQUALS';
        $matches = 'MATCHES';
        $in      = 'IN';

        return [
            [
                'foo is "bar"',
                $this->stub('foo', $eq, 'bar')
            ],
            [
                'foo is true',
                $this->stub('foo', $eq, true)
            ],
            [
                'foo is false',
                $this->stub('foo', $eq, false)
            ],
            [
                'foo matches "/pattern/i"',
                $this->stub('foo', $matches, '/pattern/i')
            ],
            [
                'foo > 1',
                $this->stub('foo', $gt, 1)
            ],
            [
                'foo >= 1',
                $this->stub('foo', $gte, 1)
            ],
            [
                'foo < 1',
                $this->stub('foo', $lt, 1)
            ],
            [
                'foo <= 1',
                $this->stub('foo', $lte, 1)
            ],
            [
                'foo not "bar"',
                new Not($this->stub('foo', $eq, 'bar'))
            ],
            [
                'foo not is "bar"',
                new Not($this->stub('foo', $eq, 'bar'))
            ],
            [
                'foo not matches "/pattern/i"',
                new Not($this->stub('foo', $matches, '/pattern/i'))
            ],
            [
                'foo in ["a", "b", "c"]',
                $this->stub('foo', $in, ['a', 'b', 'c'])
            ],
            [
                'foo in ["a", 2, 3.5]',
                $this->stub('foo', $in, ['a', 2, 3.5])
            ],
            [
                'foo is "bar" or (deep is 1 and (deeper is 2))',
                Composite::or(
                    $this->stub('foo', $eq, 'bar'),
                    Composite::and(
                        $this->stub('deep', $eq, 1),
                        $this->stub('deeper', $eq, 2)
                    )
                )
            ],
            [
                '(foo is "bar") and (a is 1 or b is 2) and (c is "red" or d is "blue")',
                Composite::and(
                    $this->stub('foo', $eq, 'bar'),
                    Composite::or(
                        $this->stub('a', $eq, 1),
                        $this->stub('b', $eq, 2)
                    ),
                    Composite::or(
                        $this->stub('c', $eq, 'red'),
                        $this->stub('d', $eq, 'blue')
                    )
                )
            ],
            [
                'foo is 1 or foo is 2 or foo is 3 or foo is 4',
                Composite::or(
                    $this->stub('foo', $eq, 1),
                    $this->stub('foo', $eq, 2),
                    $this->stub('foo', $eq, 3),
                    $this->stub('foo', $eq, 4)
                )
            ],
            [
                'foo is 1 or foo is 2 and foo is 3',
                Composite::and(
                    Composite::or(
                        $this->stub('foo', $eq, 1),
                        $this->stub('foo', $eq, 2)
                    ),
                    $this->stub('foo', $eq, 3)
                )
            ],
            [
                '(foo is 1 or foo is 2) and foo is 3',
                Composite::and(
                    Composite::or(
                        $this->stub('foo', $eq, 1),
                        $this->stub('foo', $eq, 2)
                    ),
                    $this->stub('foo', $eq, 3)
                )
            ],
            [
                'foo is 1 or (foo is 2 and foo is 3)',
                Composite::or(
                    $this->stub('foo', $eq, 1),
                    Composite::and(
                        $this->stub('foo', $eq, 2),
                        $this->stub('foo', $eq, 3)
                    )
                )
            ],
            [
                'foo is 1 or (foo is 2 and foo is 3 and (foo is 4 and foo is 5))',
                Composite::or(
                    $this->stub('foo', $eq, 1),
                    Composite::and(
                        $this->stub('foo', $eq, 2),
                        $this->stub('foo', $eq, 3),
                        $this->stub('foo', $eq, 4),
                        $this->stub('foo', $eq, 5)
                    )
                )
            ],
            [
                'foo is 1 or (foo is 2 and foo is 3 and (foo is 4 and foo is 5 and foo is 6))',
                Composite::or(
                    $this->stub('foo', $eq, 1),
                    Composite::and(
                        $this->stub('foo', $eq, 2),
                        $this->stub('foo', $eq, 3),
                        $this->stub('foo', $eq, 4),
                        $this->stub('foo', $eq, 5),
                        $this->stub('foo', $eq, 6)
                    )
                )
            ],
            [
                'foo is 1 or (foo is 2 and foo is 3 and (foo is 4 and (foo is 5 and foo is 6)))',
                Composite::or(
                    $this->stub('foo', $eq, 1),
                    Composite::and(
                        $this->stub('foo', $eq, 2),
                        $this->stub('foo', $eq, 3),
                        $this->stub('foo', $eq, 4),
                        $this->stub('foo', $eq, 5),
                        $this->stub('foo', $eq, 6)
                    )
                )
            ],
            [
                'foo is 1 or (foo is 2 and foo is 3 and (foo is 4 and (foo is 5 or foo is 6)))',
                Composite::or(
                    $this->stub('foo', $eq, 1),
                    Composite::and(
                        $this->stub('foo', $eq, 2),
                        $this->stub('foo', $eq, 3),
                        $this->stub('foo', $eq, 4),
                        Composite::or(
                            $this->stub('foo', $eq, 5),
                            $this->stub('foo', $eq, 6)
                        )
                    )
                )
            ],
            [
                'foo.bar is "bar"',
                $this->stub('foo.bar', $eq, 'bar')
            ],
        ];
    }


    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        $ref = new \ReflectionObject($comparison);

        $property = $ref->getProperty('type');
        $property->setAccessible(true);

        $type = $property->getValue($comparison);

        return $this->stub($comparison->identifierFullName(), $type, $comparison->literalValue());
    }


    private function stub(string $identifier, string $comparison, $value)
    {
        return new class ($identifier, $comparison, $value) implements Specification
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
}
