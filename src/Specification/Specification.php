<?php

namespace Krixon\Rules\Specification;

interface Specification
{
    public function isSatisfiedBy($value) : bool;
}
