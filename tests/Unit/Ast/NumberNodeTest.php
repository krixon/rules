<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use Krixon\Rules\Ast\NumberNode;
use PHPUnit\Framework\TestCase;

class NumberNodeTest extends TestCase
{
    public static function testCanDetermineType() : void
    {
        static::assertSame('NUMBER', NumberNode::type());
    }
}