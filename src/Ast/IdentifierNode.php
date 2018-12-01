<?php

namespace Krixon\Rules\Ast;

class IdentifierNode extends Node
{
    private $name;
    private $subIdentifier;


    public function __construct(string $name, self $subIdentifier = null)
    {
        $this->name          = $name;
        $this->subIdentifier = $subIdentifier;
    }


    public function name() : string
    {
        return $this->name;
    }


    public function fullName() : string
    {
        $name = $this->name();

        if ($this->hasSubIdentifier()) {
            $name .= '.' . $this->getSubIdentifier()->fullName();
        }

        return $name;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitIdentifier($this);
    }


    public function getSubIdentifier() : ?self
    {
        return $this->subIdentifier;
    }


    public function hasSubIdentifier() : bool
    {
        return null !== $this->subIdentifier;
    }
}
