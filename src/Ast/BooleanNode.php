<?php

namespace Krixon\Rules\Ast;

final class BooleanNode implements LiteralNode
{
    private $value;


    private function __construct(bool $value)
    {
        $this->value = $value;
    }


    public static function true() : self
    {
        return new self(true);
    }


    public static function false() : self
    {
        return new self(false);
    }


    public function value()
    {
        return $this->value;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitBoolean($this);
    }
}