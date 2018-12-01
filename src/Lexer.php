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
    private $tokens  = [];
    private $line    = 1;  // The current line number.
    private $start   = 0;  // The position of the start of the current token.
    private $current = 0;  // The current cursor position.
    private $end     = 0;  // The position of the end of the expression.


    public function tokenize(string $expression) : array
    {
        $this->reset($expression);

        while (!$this->eof()) {
            $this->start = $this->current;
            $this->next();
        }

        $this->start = $this->current;

        $this->push(Token::EOF);

        return $this->tokens;
    }


    public function next() : void
    {
        $char = $this->advance();

        switch ($char) {
            case ' ':
            case "\r":
            case "\t":
                break;
            case "\n":
                $this->line++;
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
                    $this->error('Invalid token.');
                }
        }
    }


    /**
     * Returns the current character and then advances the cursor to the next.
     */
    private function advance() : string
    {
        return mb_substr($this->expression, $this->current++, 1);
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

        if ($position >= $this->end || $position < 0) {
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
        if ($this->eof()) {
            return false;
        }

        if ($this->peek() !== $char) {
            return false;
        }

        $this->current++;

        return true;
    }


    /**
     * Handles a possible "equals" token.
     */
    private function eq()
    {
        if (!$this->match('=')) {
            $this->error("Invalid token. Expected '=' after '='.");
            return;
        }

        $this->push(Token::EQUAL);
    }


    /**
     * Handles a possible "not equals" token.
     */
    private function neq()
    {
        if (!$this->match('=')) {
            $this->error("Invalid token. Expected '=' after '!'.");
            return;
        }

        $this->push(Token::NOT_EQUAL);
    }


    /**
     * Handles a string token.
     */
    private function string()
    {
        // TODO: Escape sequences?

        while ($this->peek() !== '"' && !$this->eof()) {
            $this->advance();
        }

        if ($this->eof()) {
            $this->error('Unterminated string.');
            return;
        }

        $value = mb_substr($this->expression, $this->start + 1, $this->current - $this->start - 1);

        $this->advance();

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
        return mb_substr($this->expression, $this->start, $this->current - $this->start);
    }


    /**
     * Determines if the end of input has been reached.
     */
    private function eof() : bool
    {
        return $this->current >= $this->end;
    }


    /**
     * Pushes a new token onto the stack.
     *
     * If no $value is provided, the current lexeme will be used, unless there is no current lexeme, in which case
     * null will be used.
     */
    private function push(string $token, $value = null)
    {
        if (null === $value) {
            $value = $this->lexeme();
            if ('' === $value) {
                $value = null;
            }
        }

        $this->tokens[] = new Token($token, $value, $this->start);
    }


    private function error(string $message) : void
    {
        // Work out the position and contents of the current line.

        for ($start = $this->start; $start > 0; $start--) {
            if (mb_substr($this->expression, $start, 1) === "\n") {
                break;
            }
        }

        for ($end = $this->start; $end < $this->end; $end++) {
            if (mb_substr($this->expression, $end, 1) === "\n") {
                break;
            }
        }

        $column  = $this->start - $start;
        $context = mb_substr($this->expression, $start, $end - $start);

        throw new SyntaxError($message, $context, $this->line, $column);
    }


    private function reset(string $expression)
    {
        $this->expression = $expression;
        $this->tokens     = [];
        $this->line       = 1;
        $this->start      = 0;
        $this->current    = 0;
        $this->end        = mb_strlen($expression);
    }
}
