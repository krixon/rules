<?php

namespace Krixon\Rules\Ast;

class LogicalNode implements Node
{
    private const AND = 'AND';
    private const OR  = 'OR';
    private const XOR = 'XOR';

    private $left;
    private $right;
    private $type;


    private function __construct(string $type, Node $left, Node $right)
    {
        $this->left  = $left;
        $this->right = $right;
        $this->type  = $type;
    }


    public function left() : Node
    {
        return $this->left;
    }


    public function right() : Node
    {
        return $this->right;
    }


    public static function and(Node $left, Node $right) : self
    {
        return new static(self::AND, $left, $right);
    }


    public static function or(Node $left, Node $right) : self
    {
        return new static(self::OR, $left, $right);
    }

    public static function xor(Node $left, Node $right) : self
    {
        return new static(self::XOR, $left, $right);
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitLogical($this);
    }


    public function isAnd() : bool
    {
        return $this->type === self::AND;
    }


    public function isOr() : bool
    {
        return $this->type === self::OR;
    }


    public function isXor() : bool
    {
        return $this->type === self::XOR;
    }
}
