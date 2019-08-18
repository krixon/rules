<?php

namespace Krixon\Rules\Ast;

use Krixon\Rules\Operator;

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
    private $identifier;
    private $operator;
    private $value;


    private function __construct(IdentifierNode $identifier, Operator $operator, LiteralNode $value)
    {
        $this->identifier = $identifier;
        $this->operator   = $operator;
        $this->value      = $value;
    }


    public static function equals(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static($left, Operator::equals(), $right);
    }


    public static function greaterThan(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static($left, Operator::greaterThan(), $right);
    }


    public static function greaterThanOrEqualTo(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static($left, Operator::greaterThanOrEquals(), $right);
    }


    public static function lessThan(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static($left, Operator::lessThan(), $right);
    }


    public static function lessThanOrEqualTo(IdentifierNode $left, LiteralNode $right) : self
    {
        return new static($left, Operator::lessThanOrEquals(), $right);
    }


    public static function in(IdentifierNode $left, LiteralNodeList $right) : self
    {
        return new static($left, Operator::in(), $right);
    }


    public static function matches(IdentifierNode $left, StringNode $right) : self
    {
        return new static($left, Operator::matches(), $right);
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitComparison($this);
    }


    public function isEquals() : bool
    {
        return $this->operator->isEquals();
    }


    public function isGreaterThan() : bool
    {
        return $this->operator->isGreaterThan();
    }


    public function isGreaterThanOrEqualTo() : bool
    {
        return $this->operator->isGreaterThanOrEqualTo();
    }


    public function isLessThan() : bool
    {
        return $this->operator->isLessThan();
    }


    public function isLessThanOrEqualTo() : bool
    {
        return $this->operator->isLessThanOrEqualTo();
    }


    public function isIn() : bool
    {
        return $this->operator->isIn();
    }


    public function isMatches() : bool
    {
        return $this->operator->isMatches();
    }


    public function isValueList() : bool
    {
        return $this->value instanceof LiteralNodeList;
    }


    public function isValueBoolean() : bool
    {
        return $this->value instanceof BooleanNode;
    }


    public function isValueString() : bool
    {
        return $this->value instanceof StringNode;
    }


    public function isValueNumber() : bool
    {
        return $this->value instanceof NumberNode;
    }


    public function isValueDate() : bool
    {
        return $this->value instanceof DateNode;
    }


    public function isValueTimezone() : bool
    {
        return $this->value instanceof TimezoneNode;
    }


    public function identifier() : IdentifierNode
    {
        return $this->identifier;
    }


    public function identifierFullName() : string
    {
        return $this->identifier->fullName();
    }


    public function operator() : Operator
    {
        return $this->operator;
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
