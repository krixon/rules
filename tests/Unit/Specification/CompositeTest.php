<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\Composite;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    use TestsSpecificationsWithChildren;


    public function testConstructable()
    {
        $or  = Composite::or($this->true(), $this->true());
        $and = Composite::and($this->true(), $this->true());

        static::assertInstanceOf(Composite::class, $or);
        static::assertInstanceOf(Composite::class, $and);
    }


    public function testReturnsFalseWhenNoChildren()
    {
        $specification = Composite::and();

        static::assertFalse($specification->isSatisfiedBy('foo'));
    }


    public function dataProvider() : array
    {
        return [
            [Composite::or($this->true()), true],
            [Composite::or($this->false()), false],
            [Composite::or($this->true(), $this->false()), true],
            [Composite::or($this->false(), $this->false()), false],
            [Composite::or($this->false(), $this->false(), $this->true()), true],
            [Composite::or($this->true(), $this->true(), $this->true()), true],

            [Composite::and($this->true()), true],
            [Composite::and($this->false()), false],
            [Composite::and($this->true(), $this->false()), false],
            [Composite::and($this->false(), $this->false()), false],
            [Composite::and($this->true(), $this->true(), $this->false()), false],
            [Composite::and($this->true(), $this->true(), $this->true()), true],

            [Composite::xor($this->true()), true],
            [Composite::xor($this->false()), false],
            [Composite::xor($this->true(), $this->false()), true],
            [Composite::xor($this->false(), $this->true()), true],
            [Composite::xor($this->false(), $this->false()), false],
            [Composite::xor($this->true(), $this->true()), false],
            [Composite::xor($this->false(), $this->false(), $this->false()), false],
            [Composite::xor($this->true(), $this->false(), $this->false()), true],
            [Composite::xor($this->true(), $this->true(), $this->false()), false],
            [Composite::xor($this->true(), $this->true(), $this->true()), false],
        ];
    }
}
