<?php

namespace Krixon\Rules\Ast;

/**
 * A simple comparison of an identifier against a single, literal value.
 *
 * For example:
 *
 * foo is "bar"
 * foo > 5
 * foo matches "/^[a-z]+$/i"
 */
class ComparisonNode implements Node
{
    private const EQUALS         = 'EQUALS';
    private const GREATER        = 'GREATER';
    private const GREATER_EQUALS = 'GREATER_EQUALS';
    private const LESS           = 'LESS';
    private const LESS_EQUALS    = 'LESS_EQUALS';
    private const IN             = 'IN';
    private const MATCHES        = 'MATCHES';

    private $identifier;
    private $value;
    private $type;


    private function __construct(string $type, IdentifierNode $identifier, LiteralNode $value)
    {
        $this->identifier = $identifier;
        $this->value      = $value;
        $this->type       = $type;
    }


    public static function equals(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static(self::EQUALS, $left, $right);
    }


    public static function greaterThan(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static(self::GREATER, $left, $right);
    }


    public static function greaterThanOrEqualTo(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static(self::GREATER_EQUALS, $left, $right);
    }


    public static function lessThan(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static(self::LESS, $left, $right);
    }


    public static function lessThanOrEqualTo(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static(self::LESS_EQUALS, $left, $right);
    }


    public static function in(IdentifierNode $left, LiteralNodeList $right) : self
    {
        return new static(self::IN, $left, $right);
    }


    public static function matches(IdentifierNode $left, StringNode $right) : self
    {
        return new static(self::MATCHES, $left, $right);
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitComparison($this);
    }


    public function isEquals() : bool
    {
        return $this->type === self::EQUALS;
    }


    public function isGreaterThan() : bool
    {
        return $this->type === self::GREATER;
    }


    public function isGreaterThanOrEqualTo() : bool
    {
        return $this->type === self::GREATER_EQUALS;
    }


    public function isLessThan() : bool
    {
        return $this->type === self::LESS;
    }


    public function isLessThanOrEqualTo() : bool
    {
        return $this->type === self::LESS_EQUALS;
    }


    public function isIn() : bool
    {
        return $this->type === self::IN;
    }


    public function isMatches() : bool
    {
        return $this->type === self::MATCHES;
    }


    public function identifier() : IdentifierNode
    {
        return $this->identifier;
    }


    public function identifierFullName() : string
    {
        return $this->identifier->fullName();
    }


    public function value() : LiteralNode
    {
        return $this->value;
    }


    /**
     * Returns the actual comparison value represented by the LiteralNode.
     */
    public function literalValue()
    {
        return $this->value->value();
    }
}
