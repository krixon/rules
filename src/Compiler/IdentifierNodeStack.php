<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Exception\CompilerError;

class IdentifierNodeStack
{
    private $wrapped;


    public function __construct()
    {
        $this->wrapped = new \SplStack();
    }


    public function push(IdentifierNode $node) : void
    {
        $this->wrapped->push($node);
    }


    /**
     * @throws CompilerError
     */
    public function pop() : IdentifierNode
    {
        try {
            return $this->wrapped->pop();
        } catch (\RuntimeException $e) {
            // Thrown when attempting to pop off the top of an empty stack.
            throw $this->error();
        }
    }


    /**
     * @throws CompilerError
     */
    public function top() : IdentifierNode
    {
        try {
            return $this->wrapped->top();
        } catch (\RuntimeException $e) {
            // Thrown when attempting to peek at the top of an empty stack.
            throw $this->error();
        }
    }


    private function error() : CompilerError
    {
        return new CompilerError('Expected to have an identifier node but none found. The supplied AST is invalid.');
    }
}