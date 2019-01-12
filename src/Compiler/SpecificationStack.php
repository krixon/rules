<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Specification;

class SpecificationStack
{
    private $wrapped;


    public function __construct()
    {
        $this->wrapped = new \SplStack();
    }


    public function push(Specification $specification) : void
    {
        $this->wrapped->push($specification);
    }


    /**
     * @throws CompilerError
     */
    public function pop() : Specification
    {
        try {
            return $this->wrapped->pop();
        } catch (\RuntimeException $e) {
            // Thrown when attempting to pop an element off an empty stack.
            throw new CompilerError('Expected to have a specification but none found. The supplied AST is invalid.');
        }
    }
}