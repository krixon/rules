<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\SpecificationError;

abstract class NumberMatchesGenerator implements SpecificationGenerator
{
    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        if (!$comparison->isValueNumber()) {
            return null;
        }

        try {
            return $this->generate($comparison->literalValue(), $comparison->operator());
        } catch (SpecificationError $exception) {
            throw CompilerError::fromSpecificationError($exception, $comparison);
        }
    }


    /**
     * @throws SpecificationError|CompilerError
     */
    abstract protected function generate(float $number, Operator $operator) : NumberMatches;
}