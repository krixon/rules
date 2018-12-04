<?php

namespace Krixon\Rules;

class Token
{
    public const LEFT_PAREN    = 'LEFT_PAREN';
    public const RIGHT_PAREN   = 'RIGHT_PAREN';
    public const LEFT_BRACKET  = 'LEFT_BRACKET';
    public const RIGHT_BRACKET = 'RIGHT_BRACKET';
    public const COMMA         = 'COMMA';
    public const DOT           = 'DOT';
    public const STRING        = 'STRING';
    public const NUMBER        = 'NUMBER';
    public const IDENTIFIER    = 'IDENTIFIER';
    public const NOT           = 'NOT';
    public const EQUAL         = 'EQUAL';
    public const GREATER_EQUAL = 'GREATER_EQUAL';
    public const GREATER       = 'GREATER';
    public const LESS_EQUAL    = 'LESS_EQUAL';
    public const LESS          = 'LESS';
    public const NOT_EQUAL     = 'NOT_EQUAL';
    public const IN            = 'IN';
    public const AND           = 'AND';
    public const OR            = 'OR';
    public const EOF           = 'EOF';

    private $value;
    private $type;
    private $position;


    public function __construct(string $type, $value, int $position)
    {
        $this->value    = $value;
        $this->type     = $type;
        $this->position = $position;
    }


    public function is(string ...$type) : bool
    {
        return in_array($this->type, $type, true);
    }


    public function isOperator() : bool
    {
        return $this->isLogicalOperator() || $this->isComparisonOperator();
    }


    public function isComparisonOperator() : bool
    {
        return $this->is(
            self::EQUAL,
            self::GREATER_EQUAL,
            self::LESS_EQUAL,
            self::NOT_EQUAL,
            self::GREATER,
            self::LESS,
            self::IN
        );
    }


    public function isLogicalOperator() : bool
    {
        return $this->is(self::AND, self::OR);
    }


    public function isLiteral() : bool
    {
        return $this->is(self::STRING, self::NUMBER);
    }


    public function type() : string
    {
        return $this->type;
    }


    public function value()
    {
        return $this->value;
    }


    public function position() : int
    {
        return $this->position;
    }
}
