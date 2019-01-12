<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\Falsy;

class FalsyTest extends SpecificationTestCase
{
    public function testConstructable()
    {
        $specification = new Falsy();

        static::assertInstanceOf(Falsy::class, $specification);
    }


    public function dataProvider() : array
    {
        return [
            [new Falsy(), true, false],
            [new Falsy(), false, true],
        ];
    }
}
