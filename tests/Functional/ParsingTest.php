<?php

namespace Krixon\Rules\Tests\Functional;

use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\ExpressionParser;
use Krixon\Rules\Ast;
use PHPUnit\Framework\TestCase;

class ParsingTest extends TestCase
{
    /**
     * @dataProvider validExpressionProvider
     */
    public function testExpectedAstIsProduced(string $expression, Ast\Node $expected)
    {
        $parser = new ExpressionParser();
        $ast    = $parser->parse($expression);

        static::assertEquals($expected, $ast);
    }


    public function validExpressionProvider()
    {
        return [
            [
                'foo is "bar"',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    new Ast\StringNode('bar')
                )
            ],
            [
                'foo is "bar" or foo is "baz"',
                Ast\LogicalNode::or(
                    Ast\ComparisonNode::equal(
                        new Ast\IdentifierNode('foo'),
                        new Ast\StringNode('bar')
                    ),
                    Ast\ComparisonNode::equal(
                        new Ast\IdentifierNode('foo'),
                        new Ast\StringNode('baz')
                    )
                )
            ],
            [
                'foo in ["bar", 42, 66.6]',
                Ast\ComparisonNode::in(
                    new Ast\IdentifierNode('foo'),
                    new Ast\NodeList(
                        new Ast\StringNode('bar'),
                        new Ast\NumberNode(42),
                        new Ast\NumberNode(66.6)
                    )
                )
            ],
            [
                'foo.bar is "baz"',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo', new Ast\IdentifierNode('bar')),
                    new Ast\StringNode('baz')
                )
            ],
        ];
    }


    /**
     * @dataProvider reportsErrorsProvider
     *
     * @param string $expression
     * @param string $message
     * @param int    $line
     * @param int    $column
     *
     * @throws SyntaxError
     */
    public function testReportsErrors(string $expression, string $message, int $line, int $column)
    {
        $parser = new ExpressionParser();

        static::expectException(SyntaxError::class);

        try {
            $parser->parse($expression);
        } catch (SyntaxError $e) {
            static::assertContains($message, $e->errorMessage());
            static::assertSame($line, $e->expressionLine());
            static::assertSame($column, $e->expressionColumn());

            throw $e;
        }
    }


    public function reportsErrorsProvider() : array
    {
        return [
            [
                'foo is (bar is "baz")',
                "Expected 'LITERAL', got 'LEFT_PAREN'.",
                1,
                8,
            ],
        ];
    }
}
