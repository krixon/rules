<?php

namespace Krixon\Rules\Specification;

use DateTimeInterface;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;

class DateMatchesGenerator implements SpecificationGenerator
{
    /**
     * @throws CompilerError
     */
    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        if (!$comparison->isValueDate()) {
            return null;
        }

        $date = $comparison->literalValue();

        if (!$date instanceof DateTimeInterface) {
            throw CompilerError::unsupportedValueType(
                $date,
                $comparison->identifierFullName(),
                DateTimeInterface::class
            );
        }

        return new DateMatches($date, $comparison->operator());
    }
}