<?php

declare(strict_types=1);

namespace Krixon\Rules\Specification;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;

abstract class RestrictableGenerator implements SpecificationGenerator
{
    use CanBeRestrictedByIdentifier;


    public function __construct(string ...$supportedIdentifiers)
    {
        $this->supportedIdentifiers = $supportedIdentifiers;
    }


    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        if (!$this->supportsIdentifier($comparison->identifier())) {
            return null;
        }

        return $this->continueAttempt($comparison);
    }


    /**
     * @throws CompilerError
     */
    abstract protected function continueAttempt(ComparisonNode $comparison) : ?Specification;
}