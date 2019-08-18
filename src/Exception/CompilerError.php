<?php

namespace Krixon\Rules\Exception;

use Exception;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\DescribesTypes;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\SpecificationError;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use Throwable;
use function sprintf;
use function vsprintf;

final class CompilerError extends Exception
{
    use DescribesTypes;

    public const GENERIC                         = 0;
    public const UNKNOWN_IDENTIFIER              = 1;
    public const UNSUPPORTED_LOGICAL_OPERATION   = 2;
    public const UNSUPPORTED_COMPARISON_OPERATOR = 3;
    public const UNSUPPORTED_VALUE_TYPE          = 4;


    public function __construct(string $message, int $code = self::GENERIC, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


    public static function unknownIdentifier(string $identifier) : self
    {
        return new self(sprintf("Unknown identifier '%s'.", $identifier), self::UNKNOWN_IDENTIFIER);
    }


    public static function unsupportedLogicalOperation() : self
    {
        return new self('Unsupported logical operation.', self::UNSUPPORTED_LOGICAL_OPERATION);
    }


    public static function unsupportedComparisonOperator(
        Operator $operator,
        string $identifier,
        string $type,
        Throwable $previous = null
    ) : self {
        $message = sprintf(
            "Unsupported comparison operator '%s' for identifier '%s' and operand type '%s'.",
            $operator,
            $identifier,
            $type
        );

        return new self($message, self::UNSUPPORTED_COMPARISON_OPERATOR, $previous);
    }


    public static function unsupportedComparisonOperatorFromNode(ComparisonNode $node, Throwable $previous = null) : self
    {
        return self::unsupportedComparisonOperator(
            $node->operator(),
            $node->identifierFullName(),
            $node->value()::type(),
            $previous
        );
    }


    public static function fromSpecificationError(SpecificationError $exception, ComparisonNode $node) : self
    {
        if ($exception instanceof UnsupportedOperator) {
            return self::unsupportedComparisonOperatorFromNode($node, $exception);
        }

        if ($exception instanceof UnsupportedValue) {
            return self::unsupportedValueType(
                $node->literalValue(),
                $node->identifierFullName(),
                $exception->expected(),
                $exception
            );
        }

        return new self('An error occurred when generating a specification.', self::GENERIC, $exception);
    }


    public static function unsupportedValueType(
        $value,
        string $identifier,
        ?string $expected = null,
        Throwable $previous = null
    ) : self {
        $message = "Unsupported value of type '%s' for identifier '%s'";
        $args    = [self::describeType($value), $identifier];

        if (null !== $expected) {
            $message .= ". Expected '%s'";
            $args[]  = $expected;
        }

        return new self(vsprintf("$message.", $args), self::UNSUPPORTED_VALUE_TYPE, $previous);
    }


    public static function unsupportedValueTypeFromNode(
        ComparisonNode $node,
        ?string $expected = null,
        Throwable $previous = null
    ) : self {
        return self::unsupportedValueType($node->literalValue(), $node->identifierFullName(), $expected, $previous);
    }
}
