<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Ast\NodeList;
use Krixon\Rules\Ast\StringNode;
use PHPUnit\Framework\TestCase;

class ComparisonNodeTest extends TestCase
{
    /**
     * @var ComparisonNode
     */
    private $in;

    /**
     * @var ComparisonNode
     */
    private $equal;

    /**
     * @var ComparisonNode
     */
    private $notEqual;


    protected function setUp()
    {
        parent::setUp();

        $identifier     = new IdentifierNode('foo');
        $value          = new StringNode('bar');
        $this->in       = ComparisonNode::in($identifier, new NodeList($value));
        $this->equal    = ComparisonNode::equal($identifier, $value);
        $this->notEqual = ComparisonNode::notEqual($identifier, $value);
    }


    public function testCanDetermineIfComparisonTypeIsIn()
    {
        static::assertTrue($this->in->isIn());
        static::assertFalse($this->equal->isIn());
        static::assertFalse($this->notEqual->isIn());
    }


    public function testCanDetermineIfComparisonTypeIsEqual()
    {
        static::assertTrue($this->equal->isEqual());
        static::assertFalse($this->in->isEqual());
        static::assertFalse($this->notEqual->isEqual());
    }


    public function testCanDetermineIfComparisonTypeIsNotEqual()
    {
        static::assertTrue($this->notEqual->isNotEqual());
        static::assertFalse($this->equal->isNotEqual());
        static::assertFalse($this->in->isNotEqual());
    }
}