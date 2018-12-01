<?php

namespace Krixon\Rules\Exception;

use Krixon\Rules\Token;

class SyntaxError extends \Exception
{
    private $errorMessage;
    private $context;
    private $expressionLine;
    private $expressionColumn;


    public function __construct(string $message, string $context, int $line, int $column)
    {
        $this->errorMessage     = $message;
        $this->context          = $context;
        $this->expressionLine   = $line;
        $this->expressionColumn = $column;

        $message = "[line $line, column $column]: $message";

        if ($context !== '') {
            $message .= "\n\n\t$line | $context\n\t" . str_repeat(' ', $column + strlen($line) + 3) . '^-- here';
        }

        parent::__construct($message);
    }


    public static function unexpectedToken(string $context, string $expected, Token $actual) : self
    {
        return new static(
            sprintf("Expected '%s', got '%s'.", $expected, $actual->type()),
            $context,
            $actual->line(),
            $actual->column()
        );
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
