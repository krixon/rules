<?php

declare(strict_types=1);

namespace Krixon\Rules\Error;

interface ErrorReporter
{
    public function report(ErrorCollection $errors) : void;
}