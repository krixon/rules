<?php

namespace Krixon\Rules\Unit\Parser;

use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Lexer\Lexer;
use Krixon\Rules\Lexer\Token;
use Krixon\Rules\Parser\DefaultParser;
use PHPUnit\Framework\TestCase;

class DefaultParserTest extends TestCase
{
    /**
     * @dataProvider unexpectedTokenProvider
     */
    public function testThrowsOnUnexpectedToken(string $expression, array $tokens, string $expected, string $got)
    {
        $lexer = $this->createMock(Lexer::class);

        $lexer->method('tokenize')->willReturn($tokens);

        /** @noinspection PhpParamsInspection */
        $parser = new DefaultParser($lexer);

        static::expectException(SyntaxError::class);
        static::expectExceptionMessage("Expected '$expected', got '$got'");

        $parser->parse($expression);
    }


    public function unexpectedTokenProvider() : array
    {
        return [
            'Missing identifier' => [
                '"foo"',
                [
                    new Token(Token::STRING, 'foo', 0),
                ],
                'IDENTIFIER',
                'STRING',
            ],
            'Missing comparison operator' => [
                'foo',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                ],
                'EQUALS | GREATER | GREATER_EQUALS | IN | LESS | LESS_EQUALS | MATCHES',
                'EOF',
            ],
            'Missing literal' => [
                'foo is',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::EQUALS, 'is', 4),
                    new Token(Token::EOF, null, 5),
                ],
                'BOOLEAN | NUMBER | STRING',
                'EOF',
            ],
            'Missing EOF token' => [
                'foo is bar',
                [
                    new Token(Token::IDENTIFIER, 'foo', 0),
                    new Token(Token::EQUALS, 'is', 4),
                    new Token(Token::STRING, 'bar', 7),
                ],
                'EOF',
                'NOTHING',
            ],
        ];
    }
}