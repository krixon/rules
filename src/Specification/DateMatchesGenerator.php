<?php

declare(strict_types=1);

namespace Krixon\Rules\Specification;

use DateTimeInterface;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\SpecificationError;

class DateMatchesGenerator extends RestrictableGenerator
{
    public function continueAttempt(ComparisonNode $comparison) : ?Specification
    {
        if (!$comparison->isValueDate()) {
            return null;
        }

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
     * @throws SpecificationError|CompilerError
     */
    protected function generate(DateTimeInterface $date, Operator $operator) : DateMatches
    {
        return new DateMatches($date, $operator);
    }
}