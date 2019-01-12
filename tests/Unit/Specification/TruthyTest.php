<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\Truthy;

class TruthyTest extends SpecificationTestCase
{
    public function testConstructable()
    {
        $specification = new Truthy();

        static::assertInstanceOf(Truthy::class, $specification);
    }


    public function dataProvider() : array
    {
        return [
            [new Truthy(), true, true],
            [new Truthy(), false, false],
        ];
    }
}
