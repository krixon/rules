<?php

namespace Krixon\Rules\Tests\Unit\Specification\Exception;

use Krixon\Rules\Specification\Exception\UnsupportedValue;
use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;

class UnsupportedValueTest extends TestCase
{
    public function testConstructable() : void
    {
        $exception = new UnsupportedValue(
            $this->createMock(Specification::class),
            'unsupported'
        );

        static::assertInstanceOf(UnsupportedValue::class, $exception);
    }
}