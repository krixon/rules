<?php

namespace Unit\Ast;

use Krixon\Rules\Ast\LogicalNode;
use Krixon\Rules\Ast\Node;
use PHPUnit\Framework\TestCase;


class LogicalNodeTest extends TestCase
{
    /**
     * @var LogicalNode
     */
    private $and;

    /**
     * @var LogicalNode
     */
    private $or;


    protected function setUp()
    {
        parent::setUp();

        $left      = $this->createMock(Node::class);
        $right     = $this->createMock(Node::class);
        $this->and = LogicalNode::and($left, $right);
        $this->or  = LogicalNode::or($left, $right);
    }


    public function testCanDetermineIfLogicalTypeIsAnd()
    {
        static::assertTrue($this->and->isAnd());
        static::assertFalse($this->or->isAnd());
    }


    public function testCanDetermineIfLogicalTypeIsOr()
    {
        static::assertTrue($this->or->isOr());
        static::assertFalse($this->and->isOr());
    }
}