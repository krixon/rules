<?php

namespace Krixon\Rules\Ast;

class LogicalNode implements Node
{
    private const AND = 0;
    private const OR  = 1;

    private $left;
    private $right;
    private $type;


    private function __construct(int $type, Node $left, Node $right)
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
}
