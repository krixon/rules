<?php

namespace Krixon\Rules\Exception;

final class CompilerError extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }


    public static function unknownIdentifier(string $identifier) : self
    {
        return new self(sprintf("Unknown identifier '%s'.", $identifier));
    }


    public static function unsupportedComparisonType(string $type, string $identifier) : self
    {
        return new CompilerError(sprintf(
            "Unsupported comparison type '%s' for identifier '%s'.",
            $type,
            $identifier
        ));
    }
}
