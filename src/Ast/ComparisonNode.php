<?php

namespace Krixon\Rules\Ast;

class ComparisonNode implements Node
{
    private const EQUAL     = 0;
    private const NOT_EQUAL = 1;
    private const IN        = 2;

    private $left;
    private $right;
    private $type;


    private function __construct(int $type, IdentifierNode $left, Node $right)
    {
        $this->left  = $left;
        $this->right = $right;
        $this->type  = $type;
    }


    public function left() : IdentifierNode
    {
        return $this->left;
    }


    public function right() : Node
    {
        return $this->right;
    }


    public static function equal(IdentifierNode $left, Node $right) : self
    {
        return new static(self::EQUAL, $left, $right);
    }


    public static function notEqual(IdentifierNode $left, Node $right) : self
    {
        return new static(self::NOT_EQUAL, $left, $right);
    }


    public static function in(IdentifierNode $left, NodeList $right) : self
    {
        return new static(self::IN, $left, $right);
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitComparison($this);
    }


    public function isEqual() : bool
    {
        return $this->type === self::EQUAL;
    }


    public function isNotEqual() : bool
    {
        return $this->type === self::NOT_EQUAL;
    }


    public function isIn() : bool
    {
        return $this->type === self::IN;
    }
}
