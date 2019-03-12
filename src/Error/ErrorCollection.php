<?php

declare(strict_types=1);

namespace Krixon\Rules\Error;

class ErrorCollection
{
    private $errors = [];


    public function append(\Krixon\Rules\Exception\SyntaxError $error) : void
    {
        $this->errors[] = $error;
    }


    public function count() : int
    {
        return count($this->errors);
    }
}