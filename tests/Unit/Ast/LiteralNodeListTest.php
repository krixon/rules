<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use Krixon\Rules\Ast\LiteralNodeList;
use PHPUnit\Framework\TestCase;

class LiteralNodeListTest extends TestCase
{
    public static function testCanDetermineType() : void
    {
        static::assertSame('list', LiteralNodeList::type());
    }
}