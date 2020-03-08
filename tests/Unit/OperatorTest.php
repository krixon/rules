<?php

declare(strict_types=1);

namespace Krixon\Rules\Tests\Unit;

use Krixon\Rules\Operator;
use PHPUnit\Framework\TestCase;

class OperatorTest extends TestCase
{
    /**
     * @dataProvider stringRepresentationProvider
     */
    public function testStringRepresentation(Operator $operator, string $expected) : void
    {
        static::assertSame($expected, (string) $operator);
    }


    public static function stringRepresentationProvider() : array
    {
        return [
            [Operator::containsAll(), 'contains all'],
            [Operator::containsAny(), 'contains any'],
            [Operator::equals(), 'is'],
            [Operator::lessThan(), '<'],
            [Operator::lessThanOrEquals(), '<='],
            [Operator::greaterThan(), '>'],
            [Operator::greaterThanOrEquals(), '>='],
            [Operator::matches(), 'matches'],
            [Operator::in(), 'in'],
        ];
    }
}
