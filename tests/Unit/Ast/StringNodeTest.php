<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use Krixon\Rules\Ast\StringNode;
use PHPUnit\Framework\TestCase;

class StringNodeTest extends TestCase
{
    public static function testCanDetermineType() : void
    {
        static::assertSame('string', StringNode::type());
    }
}