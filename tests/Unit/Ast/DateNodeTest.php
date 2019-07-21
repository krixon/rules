<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use Krixon\Rules\Ast\DateNode;
use PHPUnit\Framework\TestCase;

class DateNodeTest extends TestCase
{
    public static function testCanDetermineType() : void
    {
        static::assertSame('DATE', DateNode::type());
    }
}