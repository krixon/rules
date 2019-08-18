<?php

namespace Krixon\Rules\Tests\Functional;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Any;
use Krixon\Rules\Specification\Composite;
use Krixon\Rules\Specification\Not;
use Krixon\Rules\Specification\Specification;

class SuccessfulCompilationTest extends CompilerTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testCompiles(string $expression, Specification $expected) : void
    {
        static::assertEquals($expected, $this->compile($expression));
    }


    public function dataProvider() : array
    {
        $eq      = Operator::equals();
        $gt      = Operator::greaterThan();
        $gte     = Operator::greaterThanOrEquals();
        $lt      = Operator::lessThan();
        $lte     = Operator::lessThanOrEquals();
        $matches = Operator::matches();
        $in      = Operator::in();

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
                Composite::or(
                    $this->stub('foo', $eq, 'a'),
                    $this->stub('foo', $eq, 'b'),
                    $this->stub('foo', $eq, 'c')
                )
            ],
            [
                'foo in [1, 2, 3.5]',
                Composite::or(
                    $this->stub('foo', $eq, 1),
                    $this->stub('foo', $eq, 2),
                    $this->stub('foo', $eq, 3.5)
                )
            ],
            'Between, simple numbers' => [
                'foo between 10 and 20',
                Composite::and(
                    $this->stub('foo', $gte, 10),
                    $this->stub('foo', $lte, 20)
                )
            ],
            'Between, simple strings' => [
                'foo between "a" and "z"',
                Composite::and(
                    $this->stub('foo', $gte, 'a'),
                    $this->stub('foo', $lte, 'z')
                )
            ],
            'Between, interval notation ()' => [
                'foo between (10, 20)',
                Composite::and(
                    $this->stub('foo', $gt, 10),
                    $this->stub('foo', $lt, 20)
                )
            ],
            'Between, interval notation []' => [
                'foo between [10, 20]',
                Composite::and(
                    $this->stub('foo', $gte, 10),
                    $this->stub('foo', $lte, 20)
                )
            ],
            'Between, interval notation (]' => [
                'foo between (10, 20]',
                Composite::and(
                    $this->stub('foo', $gt, 10),
                    $this->stub('foo', $lte, 20)
                )
            ],
            'Between, interval notation [)' => [
                'foo between [10, 20)',
                Composite::and(
                    $this->stub('foo', $gte, 10),
                    $this->stub('foo', $lt, 20)
                )
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
                'foo is 10 xor bar is 10',
                Composite::xor(
                    $this->stub('foo', $eq, 10),
                    $this->stub('bar', $eq, 10)
                )
            ],
            [
                'foo is 10 xor bar is 10 xor baz is 10',
                Composite::xor(
                    $this->stub('foo', $eq, 10),
                    $this->stub('bar', $eq, 10),
                    $this->stub('baz', $eq, 10)
                )
            ],
            [
                'foo.bar is "bar"',
                $this->stub('foo.bar', $eq, 'bar')
            ],
        ];
    }
}
