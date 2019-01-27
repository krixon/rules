<?php

namespace Krixon\Rules\Tests\Unit\Lexer;

use Krixon\Rules\Lexer\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testExposesType()
    {
        $token = new Token(Token::LEFT_PAREN, '(', 0);

        static::assertSame(Token::LEFT_PAREN, $token->type());
    }


    public function testExposesValue()
    {
        $token = new Token(Token::LEFT_PAREN, '(', 0);

        static::assertSame('(', $token->value());
    }


    public function testExposesPosition()
    {
        $token = new Token(Token::LEFT_PAREN, '(', 0);

        static::assertSame(0, $token->position());
    }


    /**
     * @dataProvider literalProvider
     */
    public function testCanDetermineIfLiteral(Token $token, bool $expected)
    {
        static::assertSame($expected, $token->isLiteral());
    }


    public function literalProvider() : array
    {
        return [
            [new Token(Token::STRING, 'foo', 0), true],
            [new Token(Token::NUMBER, 12345, 0), true],
            [new Token(Token::DOT, '.', 0), false],
            [new Token(Token::EOF, null, 0), false],
        ];
    }
}