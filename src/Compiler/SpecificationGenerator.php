<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Specification;

interface SpecificationGenerator
{
    /**
     * @throws CompilerError
     */
    public function attempt(ComparisonNode $comparison) : ?Specification;
}