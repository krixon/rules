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
    private const CONTAINS_ANY   = 'CONTAINS_ANY';
    private const CONTAINS_ALL   = 'CONTAINS_ALL';

    private $operator;


    private function __construct(string $operator)
    {
        $this->operator = $operator;
    }


    public function __toString() : string
    {
        switch ($this->operator) {
            case self::EQUALS:         return 'is';
            case self::LESS:           return '<';
            case self::LESS_EQUALS:    return '<=';
            case self::GREATER:        return '>';
            case self::GREATER_EQUALS: return '>=';
            case self::IN:             return 'in';
            case self::MATCHES:        return 'matches';
            case self::CONTAINS_ALL:   return 'contains all';
            case self::CONTAINS_ANY:   return 'contains any';
        }

        // @codeCoverageIgnoreStart
        // It is not possible to reach this point in a bug-free implementation.
        return 'UNKNOWN';
        // @codeCoverageIgnoreEnd
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


    public static function containsAny() : self
    {
        return new self(self::CONTAINS_ANY);
    }


    public static function containsAll() : self
    {
        return new self(self::CONTAINS_ALL);
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


    public function isContains() : bool
    {
        return $this->isContainsAny() || $this->isContainsAll();
    }


    public function isContainsAny() : bool
    {
        return $this->operator === self::CONTAINS_ANY;
    }


    public function isContainsAll() : bool
    {
        return $this->operator === self::CONTAINS_ALL;
    }
}
