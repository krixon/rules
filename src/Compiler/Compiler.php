<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Ast\Node;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Specification;

interface Compiler
{
    /**
     * Compiles an AST into a Specification.
     *
     * @throws CompilerError
     */
    public function compile(Node $node, ?Options $options = null) : Specification;
}