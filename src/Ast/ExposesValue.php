<?php

namespace Krixon\Rules\Ast;

trait ExposesValue
{
    private $value;


    public function value()
    {
        return $this->value;
    }
}