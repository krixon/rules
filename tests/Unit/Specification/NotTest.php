<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\Not;
use PHPUnit\Framework\TestCase;

class NotTest extends TestCase
{
    use TestsSpecificationsWithChildren;


    public function testConstructable()
    {
        $specification = new Not($this->true());

        static::assertInstanceOf(Not::class, $specification);
    }


    public function dataProvider() : array
    {
        return [
            [new Not(self::true()), false],
            [new Not(self::false()), true],
        ];
    }
}
