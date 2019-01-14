<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Specification\Specification;

interface SpecificationGenerator
{
    public function attempt(ComparisonNode $comparison) : ?Specification;
}