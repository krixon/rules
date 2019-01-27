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
        static::assertSame($expected, $specification->isSatisfiedBy('foo'));
    }


    private function true() : Specification
    {
        return $this->bool(true);
    }


    private function false() : Specification
    {
        return $this->bool(false);
    }


    private function bool(bool $value) : Specification
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
