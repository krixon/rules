<?php

namespace Krixon\Rules\Specification\Exception;

use DomainException;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Specification;
use Throwable;
use function get_class;

class UnsupportedOperator extends DomainException implements SpecificationError
{
    public function __construct(Specification $specification, Operator $operator, Throwable $previous = null)
    {
        $message = sprintf(
            "Unsupported operator %s for specification %s.",
            $operator,
            get_class($specification)
        );

        parent::__construct($message, 0, $previous);
    }
}