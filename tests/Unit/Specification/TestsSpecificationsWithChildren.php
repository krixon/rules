<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\Specification;

trait TestsSpecificationsWithChildren
{
    /**
     * @dataProvider dataProvider
     */
    public function testIsSatisfiedBy(Specification $specification, bool $expected) : void
    {
        static::assertSame($expected, $specification->isSatisfiedBy($expected));
    }


    private static function true() : Specification
    {
        return self::bool(true);
    }


    private static function false() : Specification
    {
        return self::bool(false);
    }


    private static function bool(bool $value) : Specification
    {
        return new class ($value) implements Specification
        {
            private $value;


            public function __construct(bool $value)
            {
                $this->value = $value;
            }


            public function isSatisfiedBy($value) : bool
            {
                return $this->value;
            }
        };
    }
}
