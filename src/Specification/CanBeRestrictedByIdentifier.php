<?php

declare(strict_types=1);

namespace Krixon\Rules\Specification;

use Krixon\Rules\Ast\IdentifierNode;
use function in_array;

/**
 * Used by specification generators which can optionally be restricted such that they will only generate
 * specifications for comparisons containing a specified identifier.
 */
trait CanBeRestrictedByIdentifier
{
    private $supportedIdentifiers = [];


    private function supportsIdentifier(IdentifierNode $identifier) : bool
    {
        if (empty($this->supportedIdentifiers)) {
            return true;
        }

        return in_array($identifier->fullName(), $this->supportedIdentifiers, true);
    }
}