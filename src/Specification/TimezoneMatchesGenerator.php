<?php

namespace Krixon\Rules\Specification;

use DateTimeZone;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\SpecificationError;

class TimezoneMatchesGenerator implements SpecificationGenerator
{
    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        try {
            return $this->generate($comparison->literalValue(), $comparison->operator());
        } catch (SpecificationError $exception) {
            throw CompilerError::fromSpecificationError($exception, $comparison);
        }
    }


    /**
     * Generates the specification with the validated options.
     *
     * This can be overridden to generate a custom specification if desired.
     *
     * @param DateTimeZone|DateTimeZone[]|string
     *
     * @throws SpecificationError|CompilerError
     */
    protected function generate($timezone, Operator $operator) : TimezoneMatches
    {
        return new TimezoneMatches($timezone, $operator);
    }
}