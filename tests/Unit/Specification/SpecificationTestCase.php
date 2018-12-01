<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;

abstract class SpecificationTestCase extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testIsSatisfiedBy(Specification $specification, $value, bool $expected) : void
    {
        static::assertSame($expected, $specification->isSatisfiedBy($value));
    }


    abstract public function dataProvider() : array;
}
