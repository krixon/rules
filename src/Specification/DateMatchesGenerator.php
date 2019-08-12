<?php

namespace Krixon\Rules\Specification;

use DateTimeInterface;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\SpecificationError;

abstract class DateMatchesGenerator implements SpecificationGenerator
{
    public function attempt(ComparisonNode $comparison) : ?Specification
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
     * @throws SpecificationError|CompilerError
     */
    abstract protected function generate(DateTimeInterface $date, Operator $operator) : DateMatches;
}