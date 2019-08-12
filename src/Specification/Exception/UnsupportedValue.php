<?php

namespace Krixon\Rules\Specification\Exception;

use DomainException;
use Krixon\Rules\Specification\Specification;
use Throwable;
use function get_class;
use function gettype;
use function vsprintf;

class UnsupportedValue extends DomainException implements SpecificationError
{
    private $expected;


    public function __construct(
        Specification $specification,
        $value,
        ?string $expected = null,
        ?Throwable $previous = null
    ) {
        $this->expected = $expected;

        $message = "Unsupported value of type %s for specification '%s'";
        $args    = [gettype($value), get_class($specification)];

        if (null !== $expected) {
            $message .= '. Expected: %s';
            $args[]  = $expected;
        }

        parent::__construct(vsprintf("$message.", $args), 0, $previous);
    }


    public function expected() : ?string
    {
        return $this->expected;
    }
}