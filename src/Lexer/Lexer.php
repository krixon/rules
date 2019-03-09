<?php

namespace Krixon\Rules\Lexer;

use Krixon\Rules\Exception\SyntaxError;

class Lexer
{
    private const KEYWORDS = [
        'is'      => Token::EQUALS,
        'not'     => Token::NOT,
        'and'     => Token::AND,
        'or'      => Token::OR,
        'xor'     => Token::XOR,
        'in'      => Token::IN,
        'true'    => Token::BOOLEAN,
        'false'   => Token::BOOLEAN,
        'matches' => Token::MATCHES,
    ];

    private const ESCAPE_SEQUENCES = [
        '"'  => '"',
        '\\' => '\\',
        'n'  => "\n",
        't'  => "\t",
    ];

    private $expression;
    private $tokens        = [];
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
    private function next() : void
    {
        $char = $this->advance();

        switch ($char) {
            case ' ':
            case "\r":
            case "\t":
            case "\n":
                break;

            case '(': $this->push(Token::LEFT_PAREN);    break;
            case ')': $this->push(Token::RIGHT_PAREN);   break;
            case '[': $this->push(Token::LEFT_BRACKET);  break;
            case ']': $this->push(Token::RIGHT_BRACKET); break;
            case '.': $this->push(Token::DOT);           break;
            case ',': $this->push(Token::COMMA);         break;

            case '<': $this->push($this->match('=') ? Token::LESS_EQUALS    : Token::LESS);    break;
            case '>': $this->push($this->match('=') ? Token::GREATER_EQUALS : Token::GREATER); break;

            case '=': $this->eq();     break;
            case '!': $this->neq();    break;
            case '"': $this->string(); break;
            case '/': $this->comment(); break;

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
     * Advances the cursor by n characters.
     *
     * @param int $n The number of characters to consume. The default of 1 will consume the current character only.
     */
    private function consume(int $n = 1) : void
    {
        $this->current += $n;
    }


    /**
     * Peeks at a character without advancing the cursor.
     *
     * @param int $offset The number of characters ahead to peek. Defaults to 0 to peek at the current character.
     * @param int $length The maximum number of characters to return. Fewer characters will be returned if not enough
     *                    remain in the expression from the specified offset.
     *
     * @return string|null The character or null of the offset results in a position before or after the expression.
     */
    private function peek(int $offset = 0, int $length = 1) : ?string
    {
        $position = $this->current + $offset;

        if ($position >= $this->expressionEnd || $position < 0) {
            return null;
        }

        return mb_substr($this->expression, $position, $length);
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

        $this->push(Token::EQUALS);
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

        $this->push(Token::NOT);
    }


    /**
     * Handles a string token.
     *
     * @throws SyntaxError
     */
    private function string()
    {
        $buffer = '';

        while ($this->peek() !== '"' && !$this->eof()) {
            $current = $this->peek();

            // Handle escape sequences.
            if ($current === '\\') {
                $next = $this->peek(1);
                if (array_key_exists($next, self::ESCAPE_SEQUENCES)) {
                    $current = self::ESCAPE_SEQUENCES[$next];
                    $this->consume();
                }
            }

            $buffer .= $current;

            $this->consume();
        }

        if ($this->eof()) {
            throw new SyntaxError('Unterminated string.', $this->expression, $this->current);
        }

        $this->consume();

        $this->push(Token::STRING, $buffer);
    }


    /**
     * @throws SyntaxError
     */
    private function comment() : void
    {
        if ($this->match('/')) {
            $this->lineComment();
        } elseif ($this->match('*')) {
            $this->blockComment();
        }
    }


    private function lineComment() : void
    {
        while ($this->peek() !== "\n" && !$this->eof()) {
            $this->advance();
        }
    }


    /**
     * @throws SyntaxError
     */
    private function blockComment() : void
    {
        /* This kind of comment continues until it is closed. It can also be nested. */
        $balance = 1;

        while (!$this->eof()) {
            $chars = $this->peek(0, 2);

            if ($chars === '*/') {
                --$balance;
            } elseif ($chars === '/*') {
                ++$balance;
            }

            if ($balance === 0) {
                break;
            }

            $this->consume();
        }

        if ($this->peek(0, 2) === '*/') {
            $this->consume(2);
        } else {
            throw new SyntaxError('Unclosed block comment.', $this->expression, $this->current);
        }
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
        $this->tokenStart    = 0;
        $this->current       = 0;
        $this->expressionEnd = mb_strlen($expression);
    }
}
