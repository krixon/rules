<?php

declare(strict_types=1);

namespace Krixon\Rules\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Exception\SpecificationError;

class DateMatchesGenerator extends RestrictableGenerator
{
    public function continueAttempt(ComparisonNode $comparison) : ?Specification
    {
        if (!$comparison->isValueDate()) {
            return null;
        }

        try {
            return $this->generate($comparison);
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
    protected function generate(ComparisonNode $comparison) : DateMatches
    {
        return new DateMatches($comparison->literalValue(), $comparison->operator());
    }
}