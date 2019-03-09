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

    /**
     * @var LogicalNode
     */
    private $xor;


    protected function setUp()
    {
        parent::setUp();

        $left      = $this->createMock(Node::class);
        $right     = $this->createMock(Node::class);
        $this->and = LogicalNode::and($left, $right);
        $this->or  = LogicalNode::or($left, $right);
        $this->xor = LogicalNode::xor($left, $right);
    }


    public function testCanDetermineIfLogicalTypeIsAnd()
    {
        static::assertTrue($this->and->isAnd());
        static::assertFalse($this->or->isAnd());
        static::assertFalse($this->xor->isAnd());
    }


    public function testCanDetermineIfLogicalTypeIsOr()
    {
        static::assertTrue($this->or->isOr());
        static::assertFalse($this->and->isOr());
        static::assertFalse($this->xor->isOr());
    }


    public function testCanDetermineIfLogicalTypeIsXor()
    {
        static::assertTrue($this->xor->isXor());
        static::assertFalse($this->and->isXor());
        static::assertFalse($this->or->isXor());
    }
}