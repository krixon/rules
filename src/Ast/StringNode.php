<?php

namespace Krixon\Rules\Ast;

/**
 * @method string value()
 */
final class StringNode implements LiteralNode
{
    use ExposesValue;


    public function __construct(string $value)
    {
        $this->value = $value;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitString($this);
    }
}
