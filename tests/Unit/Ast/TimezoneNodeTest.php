<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use Krixon\Rules\Ast\TimezoneNode;
use PHPUnit\Framework\TestCase;

class TimezoneNodeTest extends TestCase
{
    public static function testCanDetermineType() : void
    {
        static::assertSame('TIMEZONE', TimezoneNode::type());
    }
}