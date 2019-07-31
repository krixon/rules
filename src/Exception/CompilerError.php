<?php

namespace Krixon\Rules\Exception;

use Exception;
use Krixon\Rules\Operator;
use function vsprintf;

final class CompilerError extends Exception
{
    public const GENERIC                         = 0;
    public const UNKNOWN_IDENTIFIER              = 1;
    public const UNKNOWN_COMPARISON_TYPE         = 2;
    public const UNSUPPORTED_COMPARISON_OPERATOR = 3;
    public const UNSUPPORTED_VALUE_TYPE          = 4;


    public function __construct(string $message, int $code = self::GENERIC)
    {
        parent::__construct($message, $code);
    }


    public static function unknownIdentifier(string $identifier) : self
    {
        return new self(sprintf("Unknown identifier '%s'.", $identifier), self::UNKNOWN_IDENTIFIER);
    }


    public static function unknownComparisonType() : self
    {
        // @codeCoverageIgnoreStart
        // It is not expected that this is ever thrown in a bug-free implementation.
        return new CompilerError('Unknown comparison type.', self::UNKNOWN_COMPARISON_TYPE);
        // @codeCoverageIgnoreEnd
    }


    public static function unsupportedComparisonOperator(
        Operator $operator,
        string $identifier,
        ?string $literalType = null
    ) : self
    {
        $message = "Unsupported comparison operator '%s' for identifier '%s'";
        $args    = [$operator, $identifier];

        if (null !== $literalType) {
            $message .= " and operand type '%s'";
            $args[]  = $literalType;
        }

        return new CompilerError(vsprintf("$message.", $args), self::UNSUPPORTED_COMPARISON_OPERATOR);
    }


    public static function unsupportedValueType(
        $value,
        string $identifier,
        ?string $expected = null
    ) : self
    {
        $message = "Unsupported value of type %s for identifier '%s'";
        $args    = [gettype($value), $identifier];

        if (null !== $expected) {
            $message .= ". Expected: %s";
            $args[]  = $expected;
        }

        return new CompilerError(vsprintf("$message.", $args), self::UNSUPPORTED_VALUE_TYPE);
    }
}
