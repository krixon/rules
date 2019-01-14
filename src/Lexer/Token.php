<?php

namespace Krixon\Rules\Lexer;

class Token
{
    public const LEFT_PAREN    = 'LEFT_PAREN';
    public const RIGHT_PAREN    = 'RIGHT_PAREN';
    public const LEFT_BRACKET   = 'LEFT_BRACKET';
    public const RIGHT_BRACKET  = 'RIGHT_BRACKET';
    public const COMMA          = 'COMMA';
    public const DOT            = 'DOT';
    public const STRING         = 'STRING';
    public const NUMBER         = 'NUMBER';
    public const BOOLEAN        = 'BOOLEAN';
    public const IDENTIFIER     = 'IDENTIFIER';
    public const NOT            = 'NOT';
    public const EQUALS         = 'EQUALS';
    public const GREATER_EQUALS = 'GREATER_EQUALS';
    public const GREATER        = 'GREATER';
    public const LESS_EQUALS    = 'LESS_EQUALS';
    public const LESS           = 'LESS';
    public const IN             = 'IN';
    public const MATCHES        = 'MATCHES';
    public const AND            = 'AND';
    public const OR             = 'OR';
    public const EOF            = 'EOF';

    public const COMPARISON_OPERATORS = [
        self::EQUALS,
        self::GREATER_EQUALS,
        self::LESS_EQUALS,
        self::GREATER,
        self::LESS,
        self::IN,
        self::MATCHES,
    ];

    // Technically NOT is a logical operator, but this list only includes binary operators
    // used to group comparisons. NOT is only used to invert a comparison operator.
    public const LOGICAL_OPERATORS = [
        self::AND,
        self::OR,
    ];

    public const OPERATORS = [
        self::EQUALS,
        self::GREATER_EQUALS,
        self::LESS_EQUALS,
        self::GREATER,
        self::LESS,
        self::IN,
        self::MATCHES,
        self::AND,
        self::OR,
    ];

    public const LITERALS = [
        self::STRING,
        self::NUMBER,
        self::BOOLEAN,
    ];

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


    public function isLiteral() : bool
    {
        return $this->is(...self::LITERALS);
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
