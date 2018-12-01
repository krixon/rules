<?php

namespace Krixon\Rules\Exception;

class CompilerError extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
