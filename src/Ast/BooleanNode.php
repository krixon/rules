<?php

namespace Krixon\Rules\Ast;

/**
 * @method bool value()
 */
final class BooleanNode implements LiteralNode
{
    use ExposesValue;


    public function __construct(bool $value)
    {
        $this->value = $value;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitBoolean($this);
    }
}