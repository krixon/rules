<?php

namespace Krixon\Rules\Specification;

class Not implements Specification
{
    private $child;


    public function __construct(Specification $child)
    {
        $this->child = $child;
    }


    public function isSatisfiedBy($value) : bool
    {
        return !$this->child->isSatisfiedBy($value);
    }
}
