<?php

namespace Krixon\Rules\Tests\Unit\Lexer;

use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Lexer\Lexer;
use Krixon\Rules\Lexer\Token;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    /**
     * @dataProvider expressionProvider
     * @param string $expression
     * @param Token[] $expected
     * @throws SyntaxError
     */
    public function testProducesExpectedTokens(string $expression, array $expected)
    {
        $lexer = new Lexer();

        $tokens = $lexer->tokenize($expression);

        static::assertEquals($expected, $tokens);
    }


    public function expressionProvider() : array
    {
        return [
            [
                '',
                [new Token(Token::EOF, null, 0)],
            ],
            [
                "\t\t\n",
                [new Token(Token::EOF, null, 3)],
            ],
            [
                'foo is "bar"',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::EQUALS, 'is', 4),
                    new Token(Token::STRING, 'bar', 7),
                    new Token(Token::EOF, null, 12),
                ],
            ],
            [
                'foo == "bar"',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::EQUALS, '==', 4),
                    new Token(Token::STRING, 'bar', 7),
                    new Token(Token::EOF, null, 12),
                ],
            ],
            [
                'foo not "bar"',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::NOT, 'not', 4),
                    new Token(Token::STRING, 'bar', 8),
                    new Token(Token::EOF, null, 13),
                ],
            ],
            [
                'foo != "bar"',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::NOT, '!=', 4),
                    new Token(Token::STRING, 'bar', 7),
                    new Token(Token::EOF, null, 12),
                ],
            ],
            [
                'foo is 1 or foo is 2',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::EQUALS, 'is', 4),
                    new Token(Token::NUMBER, 1, 7),
                    new Token(Token:: OR, 'or', 9),
                    new Token(Token::IDENTIFIER, 'foo', 12),
                    new Token(Token::EQUALS, 'is', 16),
                    new Token(Token::NUMBER, 2, 19),
                    new Token(Token::EOF, null, 20),
                ],
            ],
            [
                'foo is 1 and (bar > 100 or bar in [42, 43])',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::EQUALS, 'is', 4),
                    new Token(Token::NUMBER, 1, 7),
                    new Token(Token:: AND, 'and', 9),
                    new Token(Token::LEFT_PAREN, '(', 13),
                    new Token(Token::IDENTIFIER, 'bar', 14),
                    new Token(Token::GREATER, '>', 18),
                    new Token(Token::NUMBER, 100, 20),
                    new Token(Token:: OR, 'or', 24),
                    new Token(Token::IDENTIFIER, 'bar', 27),
                    new Token(Token::IN, 'in', 31),
                    new Token(Token::LEFT_BRACKET, '[', 34),
                    new Token(Token::NUMBER, 42, 35),
                    new Token(Token::COMMA, ',', 37),
                    new Token(Token::NUMBER, 43, 39),
                    new Token(Token::RIGHT_BRACKET, ']', 41),
                    new Token(Token::RIGHT_PAREN, ')', 42),
                    new Token(Token::EOF, null, 43),
                ],
            ],
            [
                'foo.bar is 42',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::DOT, '.', 3),
                    new Token(Token::IDENTIFIER, 'bar', 4),
                    new Token(Token::EQUALS, 'is', 8),
                    new Token(Token::NUMBER, 42, 11),
                    new Token(Token::EOF, null, 13),
                ],
            ],
            [
                'Ģħİ is "ŤŮŴ"',
                [
                    new Token(Token::IDENTIFIER, 'Ģħİ', 0),
                    new Token(Token::EQUALS, 'is', 4),
                    new Token(Token::STRING, 'ŤŮŴ', 7),
                    new Token(Token::EOF, null, 12),
                ],
            ],
            [
                'foo is date:"2019-01-01T12:02:15Z"',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::EQUALS, 'is', 4),
                    new Token(Token::IDENTIFIER, 'date', 7),
                    new Token(Token::COLON, ':', 11),
                    new Token(Token::STRING, '2019-01-01T12:02:15Z', 12),
                    new Token(Token::EOF, null, 34),
                ],
            ],
        ];
    }


    /**
     * @dataProvider reportsErrorsProvider
     */
    public function testReportsErrors(string $expression, string $message, int $line, int $column)
    {
        $lexer = new Lexer();

        static::expectException(SyntaxError::class);

        try {
            $lexer->tokenize($expression);
        } catch (SyntaxError $e) {
            static::assertStringContainsStringIgnoringCase($message, $e->errorMessage());
            static::assertSame($line, $e->expressionLine());
            static::assertSame($column, $e->expressionColumn());

            throw $e;
        }
    }


    public function reportsErrorsProvider() : array
    {
        return [
            [
                'foo ! bar',
                "Expected '=', got ' '",
                1,
                5,
            ],
            [
                'foo = bar',
                "Expected '=', got ' '",
                1,
                5,
            ],
            [
                'foo => "bar',
                "Expected '=', got '>'",
                1,
                5,
            ],
            [
                'foo is "bar',
                "Unterminated string",
                1,
                12,
            ],
            [
                '~',
                "Invalid token",
                1,
                1,
            ],
        ];
    }
}
