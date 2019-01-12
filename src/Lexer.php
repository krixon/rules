<?php

namespace Krixon\Rules;

use Krixon\Rules\Exception\SyntaxError;

class Lexer
{
    private const KEYWORDS = [
        'is'  => Token::EQUAL,
        'not' => Token::NOT_EQUAL,
        'and' => Token::AND,
        'or'  => Token::OR,
        'in'  => Token::IN,
    ];

    private $expression;
    private $tokens        = [];
    private $lineNumber    = 1;  // The current line number. 1-based.
    private $lineOffset    = 0;  // The offset into $expression of the start of the current line.
    private $tokenStart    = 0;  // The position of the start of the current token.
    private $current       = 0;  // The current cursor position (the offset into $expression).
    private $expressionEnd = 0;  // The position of the end of the expression.


    /**
     * @return Token[]
     * @throws SyntaxError
     */
    public function tokenize(string $expression) : array
    {
        $this->reset($expression);

        while (!$this->eof()) {
            $this->tokenStart = $this->current;
            $this->next();
        }

        $this->tokenStart = $this->current;

        $this->push(Token::EOF);

        return $this->tokens;
    }


    /**
     * @throws SyntaxError
     */
    public function next() : void
    {
        $char = $this->advance();

        switch ($char) {
            case ' ':
            case "\r":
            case "\t":
                break;
            case "\n":
                $this->lineNumber++;
                $this->lineOffset = $this->current;
                break;

            case '(': $this->push(Token::LEFT_PAREN);    break;
            case ')': $this->push(Token::RIGHT_PAREN);   break;
            case '[': $this->push(Token::LEFT_BRACKET);  break;
            case ']': $this->push(Token::RIGHT_BRACKET); break;
            case '.': $this->push(Token::DOT);           break;
            case ',': $this->push(Token::COMMA);         break;

            case '<': $this->push($this->match('=') ? Token::LESS_EQUAL    : Token::LESS);    break;
            case '>': $this->push($this->match('=') ? Token::GREATER_EQUAL : Token::GREATER); break;

            case '=': $this->eq();     break;
            case '!': $this->neq();    break;
            case '"': $this->string(); break;

            default:
                if (ctype_digit($char)) {
                    $this->number();
                } elseif (\IntlChar::isalpha($char)) {
                    $this->identifier();
                } else {
                    throw new SyntaxError('Invalid token.', $this->expression, $this->current - 1);
                }
        }
    }


    /**
     * Returns the current character and advances the cursor to the next.
     */
    private function advance() : string
    {
        $this->consume();

        return mb_substr($this->expression, $this->current - 1, 1);
    }


    /**
     * Consumes the current character and advances the cursor to the next.
     */
    private function consume() : void
    {
        $this->current++;
    }


    /**
     * Peeks at a character without advancing the cursor.
     *
     * @param int $offset The number of characters ahead to peek. Defaults to 0 to peek at the current character.
     *
     * @return string|null The character or null of the offset results in a position before or after the expression.
     */
    private function peek(int $offset = 0) : ?string
    {
        $position = $this->current + $offset;

        if ($position >= $this->expressionEnd || $position < 0) {
            return null;
        }

        return mb_substr($this->expression, $position, 1);
    }


    /**
     * Determines if the current character matches the specified character. If so, the cursor is advanced, consuming
     * the current character.
     */
    private function match(string $char) : bool
    {
        if ($this->peek() !== $char) {
            return false;
        }

        $this->consume();

        return true;
    }


    /**
     * Handles a possible "equals" token.
     *
     * @throws SyntaxError
     */
    private function eq()
    {
        if (!$this->match('=')) {
            $this->unexpectedCharacter('=');
        }

        $this->push(Token::EQUAL);
    }


    /**
     * Handles a possible "not equals" token.
     *
     * @throws SyntaxError
     */
    private function neq()
    {
        if (!$this->match('=')) {
            $this->unexpectedCharacter('=');
        }

        $this->push(Token::NOT_EQUAL);
    }


    /**
     * Handles a string token.
     *
     * @throws SyntaxError
     */
    private function string()
    {
        // TODO: Escape sequences?

        while ($this->peek() !== '"' && !$this->eof()) {
            $this->consume();
        }

        if ($this->eof()) {
            throw new SyntaxError('Unterminated string.', $this->expression, $this->current);
        }

        $value = mb_substr($this->expression, $this->tokenStart + 1, $this->current - $this->tokenStart - 1);

        $this->consume();

        $this->push(Token::STRING, $value);
    }


    /**
     * Handles a number token.
     */
    private function number()
    {
        while (ctype_digit($this->peek())) {
            $this->advance();
        }

        // Fractional component to support floats.
        if ($this->peek() === '.' && ctype_digit($this->peek(1))) {
            $this->advance();
            while (ctype_digit($this->peek())) {
                $this->advance();
            }
        }

        $value = (float)$this->lexeme();

        $this->push(Token::NUMBER, $value);
    }


    /**
     * Handles an identifier token.
     */
    private function identifier()
    {
        while (\IntlChar::isalnum($this->peek())) {
            $this->advance();
        }

        // Check if the identifier is a keyword.
        $value = $this->lexeme();

        if (array_key_exists($value, self::KEYWORDS)) {
            $this->push(self::KEYWORDS[$value]);
        } else {
            $this->push(Token::IDENTIFIER, $value);
        }
    }


    /**
     * Returns the lexeme associated with the current token.
     *
     * This is essentially the section of source code which produced the token.
     */
    private function lexeme() : string
    {
        return mb_substr($this->expression, $this->tokenStart, $this->current - $this->tokenStart);
    }


    /**
     * Determines if the end of input has been reached.
     */
    private function eof() : bool
    {
        return $this->current >= $this->expressionEnd;
    }


    /**
     * Creates a new token.
     *
     * If no $value is provided, the current lexeme will be used, unless there is no current lexeme, in which case
     * null will be used.
     */
    private function token(string $token, $value = null) : Token
    {
        if (null === $value) {
            $value = $this->lexeme();
            if ('' === $value) {
                $value = null;
            }
        }

        return new Token($token, $value, $this->tokenStart);
    }


    /**
     * Pushes a new token onto the stack.
     *
     * If no $value is provided, the current lexeme will be used, unless there is no current lexeme, in which case
     * null will be used.
     */
    private function push(string $token, $value = null) : void
    {
        $this->tokens[] = $this->token($token, $value);
    }


    /**
     * @throws SyntaxError
     */
    private function unexpectedCharacter(string $expected, string $actual = null)
    {
        if (null === $actual) {
            $actual = $this->peek() ?? Token::EOF;
        }

        throw SyntaxError::unexpectedCharacter(
            $this->expression,
            $expected,
            $actual,
            $this->current - 1
        );
    }


    private function reset(string $expression)
    {
        $this->expression    = $expression;
        $this->tokens        = [];
        $this->lineNumber    = 1;
        $this->lineOffset    = 0;
        $this->tokenStart    = 0;
        $this->current       = 0;
        $this->expressionEnd = mb_strlen($expression);
    }
}
