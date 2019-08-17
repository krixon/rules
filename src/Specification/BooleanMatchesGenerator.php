<?php

declare(strict_types=1);

namespace Krixon\Rules\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Exception\SpecificationError;

class BooleanMatchesGenerator implements SpecificationGenerator
{
    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        if (!$comparison->isValueBoolean()) {
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
     * Generates the specification with the validated options.
     *
     * This can be overridden to generate a custom specification if desired.
     *
     * @throws SpecificationError|CompilerError
     */
     protected function generate(bool $value) : BooleanMatches
     {
         return new BooleanMatches($value);
     }
}