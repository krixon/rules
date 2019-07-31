<?php

namespace Krixon\Rules\Specification\Exception;

use DomainException;
use Krixon\Rules\Specification\Specification;
use Throwable;
use function get_class;
use function gettype;

class UnsupportedValue extends DomainException implements SpecificationError
{
    public function __construct(Specification $specification, $value, Throwable $previous = null)
    {
        $message = sprintf(
            'Unsupported value of type %s for specification %s.',
            gettype($value),
            get_class($specification)
        );

        parent::__construct($message, 0, $previous);
    }
}