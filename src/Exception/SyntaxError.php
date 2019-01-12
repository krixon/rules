<?php

namespace Krixon\Rules\Exception;

use Krixon\Rules\Lexer\Token;

class SyntaxError extends \Exception
{
    private $errorMessage;
    private $context;
    private $expressionLine;
    private $expressionColumn;


    public function __construct(string $message, string $expression, int $position)
    {
        $preceding = mb_substr($expression, 0, $position);

        $start = mb_strrpos($preceding, "\n");
        if ($start !== false) {
            $start++;
        } else {
            $start = 0;
        }

        $end     = mb_strpos($expression, "\n", $position) ?: null;
        $line    = substr_count($preceding, "\n") + 1;
        $column  = ($position - $start) + 1;
        $context = mb_substr($expression, $start, $end);

        $this->errorMessage     = $message;
        $this->context          = $expression;
        $this->expressionLine   = $line;
        $this->expressionColumn = $column;

        $message = "[line $line, column $column]: $message";

        if ($context !== '') {
            $message .= "\n\n\t$line | $context\n\t" . str_repeat(' ', $column + strlen($line) + 2) . '^-- here';
        }

        parent::__construct($message);
    }


    public static function unexpectedToken(string $context, string $expected, Token $actual) : self
    {
        return self::unexpectedCharacter($context, $expected, $actual->type(), $actual->position());
    }


    public static function unexpectedCharacter(
        string $context,
        string $expected,
        string $actual,
        int $position
    ) : self {
        return new static(sprintf("Expected '%s', got '%s'.", $expected, $actual), $context, $position);
    }


    public function errorMessage() : string
    {
        return $this->errorMessage;
    }


    public function context() : string
    {
        return $this->context;
    }


    public function expressionLine() : int
    {
        return $this->expressionLine;
    }


    public function expressionColumn() : int
    {
        return $this->expressionColumn;
    }
}
