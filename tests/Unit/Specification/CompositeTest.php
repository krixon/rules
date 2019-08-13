<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\Composite;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    use TestsSpecificationsWithChildren;


    public function testConstructable() : void
    {
        $or  = Composite::or(self::true(), self::true());
        $and = Composite::and(self::true(), self::true());

        static::assertInstanceOf(Composite::class, $or);
        static::assertInstanceOf(Composite::class, $and);
    }


    public function testReturnsFalseWhenNoChildren() : void
    {
        $specification = Composite::and();

        static::assertFalse($specification->isSatisfiedBy('foo'));
    }


    public function dataProvider() : array
    {
        return [
            [Composite::or(self::true()), true],
            [Composite::or(self::false()), false],
            [Composite::or(self::true(), self::false()), true],
            [Composite::or(self::false(), self::false()), false],
            [Composite::or(self::false(), self::false(), self::true()), true],
            [Composite::or(self::true(), self::true(), self::true()), true],

            [Composite::and(self::true()), true],
            [Composite::and(self::false()), false],
            [Composite::and(self::true(), self::false()), false],
            [Composite::and(self::false(), self::false()), false],
            [Composite::and(self::true(), self::true(), self::false()), false],
            [Composite::and(self::true(), self::true(), self::true()), true],

            [Composite::xor(self::true()), true],
            [Composite::xor(self::false()), false],
            [Composite::xor(self::true(), self::false()), true],
            [Composite::xor(self::false(), self::true()), true],
            [Composite::xor(self::false(), self::false()), false],
            [Composite::xor(self::true(), self::true()), false],
            [Composite::xor(self::false(), self::false(), self::false()), false],
            [Composite::xor(self::true(), self::false(), self::false()), true],
            [Composite::xor(self::true(), self::true(), self::false()), false],
            [Composite::xor(self::true(), self::true(), self::true()), false],
        ];
    }
}
