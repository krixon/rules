<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use Krixon\Rules\Ast\BooleanNode;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Ast\LiteralNodeList;
use Krixon\Rules\Ast\NumberNode;
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

    /**
     * @var IdentifierNode
     */
    private $identifier;

    /**
     * @var StringNode
     */
    private $string;

    /**
     * @var BooleanNode
     */
    private $boolean;

    /**
     * @var NumberNode
     */
    private $number;


    protected function setUp() : void
    {
        parent::setUp();

        $this->identifier = new IdentifierNode('foo');
        $this->string     = new StringNode('bar');
        $this->boolean    = new BooleanNode(true);
        $this->number     = new NumberNode(42);
        $this->equal      = ComparisonNode::equals($this->identifier, $this->string);
        $this->gt         = ComparisonNode::greaterThan($this->identifier, $this->string);
        $this->gte        = ComparisonNode::greaterThanOrEqualTo($this->identifier, $this->string);
        $this->lt         = ComparisonNode::lessThan($this->identifier, $this->string);
        $this->lte        = ComparisonNode::lessThanOrEqualTo($this->identifier, $this->string);
        $this->in         = ComparisonNode::in($this->identifier, new LiteralNodeList($this->string));
        $this->matches    = ComparisonNode::matches($this->identifier, $this->string);
    }


    public function testCanDetermineIfComparisonTypeIsIn() : void
    {
        static::assertTrue($this->in->isIn());
        static::assertFalse($this->equal->isIn());
        static::assertFalse($this->lt->isIn());
        static::assertFalse($this->lte->isIn());
        static::assertFalse($this->gt->isIn());
        static::assertFalse($this->gte->isIn());
        static::assertFalse($this->matches->isIn());
    }


    public function testCanDetermineIfComparisonTypeIsEquals() : void
    {
        static::assertTrue($this->equal->isEquals());
        static::assertFalse($this->in->isEquals());
        static::assertFalse($this->lt->isEquals());
        static::assertFalse($this->lte->isEquals());
        static::assertFalse($this->gt->isEquals());
        static::assertFalse($this->gte->isEquals());
        static::assertFalse($this->matches->isEquals());
    }


    public function testCanDetermineIfComparisonTypeIsGreaterThan() : void
    {
        static::assertTrue($this->gt->isGreaterThan());
        static::assertFalse($this->equal->isGreaterThan());
        static::assertFalse($this->in->isGreaterThan());
        static::assertFalse($this->lt->isGreaterThan());
        static::assertFalse($this->lte->isGreaterThan());
        static::assertFalse($this->gte->isGreaterThan());
        static::assertFalse($this->matches->isGreaterThan());
    }


    public function testCanDetermineIfComparisonTypeIsGreaterThanOrEqualTo() : void
    {
        static::assertTrue($this->gte->isGreaterThanOrEqualTo());
        static::assertFalse($this->equal->isGreaterThanOrEqualTo());
        static::assertFalse($this->in->isGreaterThanOrEqualTo());
        static::assertFalse($this->lt->isGreaterThanOrEqualTo());
        static::assertFalse($this->lte->isGreaterThanOrEqualTo());
        static::assertFalse($this->gt->isGreaterThanOrEqualTo());
        static::assertFalse($this->matches->isGreaterThanOrEqualTo());
    }


    public function testCanDetermineIfComparisonTypeIsLessThan() : void
    {
        static::assertTrue($this->lt->isLessThan());
        static::assertFalse($this->equal->isLessThan());
        static::assertFalse($this->in->isLessThan());
        static::assertFalse($this->lte->isLessThan());
        static::assertFalse($this->gt->isLessThan());
        static::assertFalse($this->gte->isLessThan());
        static::assertFalse($this->matches->isLessThan());
    }


    public function testCanDetermineIfComparisonTypeIsLessThanOrEqualTo() : void
    {
        static::assertTrue($this->lte->isLessThanOrEqualTo());
        static::assertFalse($this->equal->isLessThanOrEqualTo());
        static::assertFalse($this->in->isLessThanOrEqualTo());
        static::assertFalse($this->lt->isLessThanOrEqualTo());
        static::assertFalse($this->gt->isLessThanOrEqualTo());
        static::assertFalse($this->gte->isLessThanOrEqualTo());
        static::assertFalse($this->matches->isLessThanOrEqualTo());
    }


    public function testCanDetermineIfComparisonTypeIsMatches() : void
    {
        static::assertTrue($this->matches->isMatches());
        static::assertFalse($this->equal->isMatches());
        static::assertFalse($this->in->isMatches());
        static::assertFalse($this->lt->isMatches());
        static::assertFalse($this->lte->isMatches());
        static::assertFalse($this->gt->isMatches());
        static::assertFalse($this->gte->isMatches());
    }


    public function testCanDetermineIfValueIsString() : void
    {
        static::assertTrue(ComparisonNode::equals($this->identifier, $this->string)->isValueString());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->boolean)->isValueString());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->number)->isValueString());
    }


    public function testCanDetermineIfValueIsBoolean() : void
    {
        static::assertTrue(ComparisonNode::equals($this->identifier, $this->boolean)->isValueBoolean());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->string)->isValueBoolean());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->number)->isValueBoolean());
    }


    public function testCanDetermineIfValueIsNumber() : void
    {
        static::assertTrue(ComparisonNode::equals($this->identifier, $this->number)->isValueNumber());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->string)->isValueNumber());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->boolean)->isValueNumber());
    }
}