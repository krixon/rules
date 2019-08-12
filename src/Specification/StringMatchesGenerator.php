<?php

namespace Krixon\Rules\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Exception\SpecificationError;

abstract class StringMatchesGenerator implements SpecificationGenerator
{
    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        if (!$comparison->isValueString()) {
            return null;
        }

        if (!$comparison->isEquals()) {
            throw CompilerError::unsupportedComparisonOperatorFromNode($comparison);
        }

        try {
            return $this->generate($comparison->literalValue());
        } catch (SpecificationError $exception) {
            throw CompilerError::fromSpecificationError($exception, $comparison);
        }
    }


    /**
     * @throws SpecificationError|CompilerError
     */
    abstract protected function generate(string $string) : StringMatches;
}