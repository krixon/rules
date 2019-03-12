<?php

declare(strict_types=1);

namespace Krixon\Rules\Error;

use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Exception\SyntaxError;

class BufferErrorReporter implements ErrorReporter
{
    private $syntaxErrors = [];
    private $compilerErrors = [];


    public function syntaxError(SyntaxError $error) : void
    {
        $this->syntaxErrors[] = $error;
    }


    public function compilerError(CompilerError $error) : void
    {
        $this->compilerErrors[] = $error;
    }


    /**
     * @return Error[]
     */
    public function syntaxErrors() : array
    {
        return $this->syntaxErrors;
    }


    /**
     * @return CompilerError[]
     */
    public function compilerErrors() : array
    {
        return $this->compilerErrors;
    }
}