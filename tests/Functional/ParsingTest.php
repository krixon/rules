<?php

namespace Krixon\Rules\Tests\Functional;

use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Parser\DefaultParser;
use Krixon\Rules\Ast;
use PHPUnit\Framework\TestCase;

class ParsingTest extends TestCase
{
    /**
     * @dataProvider validExpressionProvider
     */
    public function testExpectedAstIsProduced(string $expression, Ast\Node $expected)
    {
        $parser = new DefaultParser();
        $ast    = $parser->parse($expression);

        static::assertEquals($expected, $ast);
    }


    public function validExpressionProvider()
    {
        return [
            'String' => [
                'foo is "bar"',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    new Ast\StringNode('bar')
                )
            ],
            'String with escape sequences' => [
                'foo is "b\"a\\z"',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    new Ast\StringNode('b"a\z')
                )
            ],
            'Multiline string using escape sequences' => [
                'foo is "b\na\nr"',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    new Ast\StringNode("b\na\nr")
                )
            ],
            'Multiline string using literal newline character' => [
                "foo is \"b\na\nr\"",
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    new Ast\StringNode("b\na\nr")
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
            'Nested identifiers, 1 level' => [
                'foo.bar is "baz"',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo', new Ast\IdentifierNode('bar')),
                    new Ast\StringNode('baz')
                )
            ],
            'Nested identifiers, 2 levels' => [
                'foo.bar.baz is "qux"',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo', new Ast\IdentifierNode('bar', new Ast\IdentifierNode('baz'))),
                    new Ast\StringNode('qux')
                )
            ],
            'Boolean true' => [
                'foo is true',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    Ast\BooleanNode::true()
                )
            ],
            'Comments are removed, // on own line' => [
                "// a comment\nfoo is true",
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    Ast\BooleanNode::true()
                )
            ],
            'Comments are removed, // at end of line' => [
                'foo is true // a comment',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    Ast\BooleanNode::true()
                )
            ],
            'Comments are removed, /**/ on own line' => [
                "/* a comment */\nfoo is true",
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    Ast\BooleanNode::true()
                )
            ],
            'Comments are removed, /**/ at start of line' => [
                '/* a comment */ foo is true',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    Ast\BooleanNode::true()
                )
            ],
            'Comments are removed, /**/ at end of line' => [
                'foo is true /* a comment */',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    Ast\BooleanNode::true()
                )
            ],
            'Comments are removed, /**/ within line' => [
                'foo is /* a comment */ true',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    Ast\BooleanNode::true()
                )
            ],
            'Comments are removed, /**/ nested' => [
                'foo is /* a /* nest/*e*/d */ comment */ true',
                Ast\ComparisonNode::equal(
                    new Ast\IdentifierNode('foo'),
                    Ast\BooleanNode::true()
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
        $parser = new DefaultParser();

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
                '',
                "Empty expression.",
                1,
                1,
            ],
            [
                'foo is (bar is "baz")',
                "Expected 'LITERAL', got 'LEFT_PAREN'.",
                1,
                8,
            ],
            [
                '/* comment',
                "Unclosed block comment.",
                1,
                11,
            ],
            [
                '"foo',
                'Unterminated string',
                1,
                5
            ],
            [
                "\"foo\nbar \nbaz",
                'Unterminated string',
                3,
                4
            ],
        ];
    }
}
