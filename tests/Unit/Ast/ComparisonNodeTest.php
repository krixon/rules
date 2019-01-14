<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Ast\LiteralNodeList;
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
    private $matches;

    /**
     * @var ComparisonNode
     */
    private $equal;

    /**
     * @var ComparisonNode
     */
    private $gt;

    /**
     * @var ComparisonNode
     */
    private $gte;

    /**
     * @var ComparisonNode
     */
    private $lt;

    /**
     * @var ComparisonNode
     */
    private $lte;


    protected function setUp()
    {
        parent::setUp();

        $identifier    = new IdentifierNode('foo');
        $value         = new StringNode('bar');
        $this->equal   = ComparisonNode::equals($identifier, $value);
        $this->gt      = ComparisonNode::greaterThan($identifier, $value);
        $this->gte     = ComparisonNode::greaterThanOrEqualTo($identifier, $value);
        $this->lt      = ComparisonNode::lessThan($identifier, $value);
        $this->lte     = ComparisonNode::lessThanOrEqualTo($identifier, $value);
        $this->in      = ComparisonNode::in($identifier, new LiteralNodeList($value));
        $this->matches = ComparisonNode::matches($identifier, $value);
    }


    public function testCanDetermineIfComparisonTypeIs()
    {
        static::assertTrue($this->in->is(ComparisonNode::IN));
        static::assertTrue($this->matches->is(ComparisonNode::MATCHES));
        static::assertTrue($this->equal->is(ComparisonNode::EQUALS));
        static::assertTrue($this->lt->is(ComparisonNode::LESS));
        static::assertTrue($this->lte->is(ComparisonNode::LESS_EQUALS));
        static::assertTrue($this->gt->is(ComparisonNode::GREATER));
        static::assertTrue($this->gte->is(ComparisonNode::GREATER_EQUALS));
    }


    public function testCanDetermineIfComparisonTypeIsIn()
    {
        static::assertTrue($this->in->isIn());
        static::assertFalse($this->equal->isIn());
        static::assertFalse($this->lt->isIn());
        static::assertFalse($this->lte->isIn());
        static::assertFalse($this->gt->isIn());
        static::assertFalse($this->gte->isIn());
        static::assertFalse($this->matches->isIn());
    }


    public function testCanDetermineIfComparisonTypeIsEquals()
    {
        static::assertTrue($this->equal->isEquals());
        static::assertFalse($this->in->isEquals());
        static::assertFalse($this->lt->isEquals());
        static::assertFalse($this->lte->isEquals());
        static::assertFalse($this->gt->isEquals());
        static::assertFalse($this->gte->isEquals());
        static::assertFalse($this->matches->isEquals());
    }


    public function testCanDetermineIfComparisonTypeIsGreaterThan()
    {
        static::assertTrue($this->gt->isGreaterThan());
        static::assertFalse($this->equal->isGreaterThan());
        static::assertFalse($this->in->isGreaterThan());
        static::assertFalse($this->lt->isGreaterThan());
        static::assertFalse($this->lte->isGreaterThan());
        static::assertFalse($this->gte->isGreaterThan());
        static::assertFalse($this->matches->isGreaterThan());
    }


    public function testCanDetermineIfComparisonTypeIsGreaterThanOrEqualTo()
    {
        static::assertTrue($this->gte->isGreaterThanOrEqualTo());
        static::assertFalse($this->equal->isGreaterThanOrEqualTo());
        static::assertFalse($this->in->isGreaterThanOrEqualTo());
        static::assertFalse($this->lt->isGreaterThanOrEqualTo());
        static::assertFalse($this->lte->isGreaterThanOrEqualTo());
        static::assertFalse($this->gt->isGreaterThanOrEqualTo());
        static::assertFalse($this->matches->isGreaterThanOrEqualTo());
    }


    public function testCanDetermineIfComparisonTypeIsLessThan()
    {
        static::assertTrue($this->lt->isLessThan());
        static::assertFalse($this->equal->isLessThan());
        static::assertFalse($this->in->isLessThan());
        static::assertFalse($this->lte->isLessThan());
        static::assertFalse($this->gt->isLessThan());
        static::assertFalse($this->gte->isLessThan());
        static::assertFalse($this->matches->isLessThan());
    }


    public function testCanDetermineIfComparisonTypeIsLessThanOrEqualTo()
    {
        static::assertTrue($this->lte->isLessThanOrEqualTo());
        static::assertFalse($this->equal->isLessThanOrEqualTo());
        static::assertFalse($this->in->isLessThanOrEqualTo());
        static::assertFalse($this->lt->isLessThanOrEqualTo());
        static::assertFalse($this->gt->isLessThanOrEqualTo());
        static::assertFalse($this->gte->isLessThanOrEqualTo());
        static::assertFalse($this->matches->isLessThanOrEqualTo());
    }


    public function testCanDetermineIfComparisonTypeIsMatches()
    {
        static::assertTrue($this->matches->isMatches());
        static::assertFalse($this->equal->isMatches());
        static::assertFalse($this->in->isMatches());
        static::assertFalse($this->lt->isMatches());
        static::assertFalse($this->lte->isMatches());
        static::assertFalse($this->gt->isMatches());
        static::assertFalse($this->gte->isMatches());
    }
}