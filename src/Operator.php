<?php

namespace Krixon\Rules;

final class Operator
{
    private const EQUALS         = 'EQUALS';
    private const LESS           = 'LESS';
    private const LESS_EQUALS    = 'LESS_EQUALS';
    private const GREATER        = 'GREATER';
    private const GREATER_EQUALS = 'GREATER_EQUALS';
    private const IN             = 'IN';
    private const MATCHES        = 'MATCHES';

    private $operator;


    private function __construct(string $operator)
    {
        $this->operator = $operator;
    }


    public function __toString() : string
    {
        return $this->operator;
    }


    public static function equals() : self
    {
        return new self(self::EQUALS);
    }


    public static function lessThan() : self
    {
        return new self(self::LESS);
    }


    public static function lessThanOrEquals() : self
    {
        return new self(self::LESS_EQUALS);
    }


    public static function greaterThan() : self
    {
        return new self(self::GREATER);
    }


    public static function greaterThanOrEquals() : self
    {
        return new self(self::GREATER_EQUALS);
    }


    public static function in() : self
    {
        return new self(self::IN);
    }


    public static function matches() : self
    {
        return new self(self::MATCHES);
    }


    public function isEquals() : bool
    {
        return $this->operator === self::EQUALS;
    }


    public function isLessThan() : bool
    {
        return $this->operator === self::LESS;
    }


    public function isLessThanOrEqualTo() : bool
    {
        return $this->operator === self::LESS_EQUALS;
    }


    public function isGreaterThan() : bool
    {
        return $this->operator === self::GREATER;
    }


    public function isGreaterThanOrEqualTo() : bool
    {
        return $this->operator === self::GREATER_EQUALS;
    }


    public function isIn() : bool
    {
        return $this->operator === self::IN;
    }


    public function isMatches() : bool
    {
        return $this->operator === self::MATCHES;
    }


    public function is(Operator ...$other) : bool
    {
        foreach ($other as $candidate) {
            if ($this->operator === $candidate->operator) {
                return true;
            }
        }

        return false;
    }
}