<?php

namespace Krixon\Rules\Tests\Unit\Ast;

use DateTimeImmutable;
use DateTimeZone;
use Krixon\Rules\Ast\BooleanNode;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Ast\DateNode;
use Krixon\Rules\Ast\IdentifierNode;
use Krixon\Rules\Ast\LiteralNodeList;
use Krixon\Rules\Ast\NumberNode;
use Krixon\Rules\Ast\StringNode;
use Krixon\Rules\Ast\TimezoneNode;
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
     * @var ComparisonNode
     */
    private $containsAny;

    /**
     * @var ComparisonNode
     */
    private $containsAll;

    /**
     * @var IdentifierNode
     */
    private $identifier;

    /**
     * @var StringNode
     */
    private $string;

    /**
     * @var LiteralNodeList
     */
    private $stringList;

    /**
     * @var BooleanNode
     */
    private $boolean;

    /**
     * @var NumberNode
     */
    private $number;

    /**
     * @var DateNode
     */
    private $date;

    /**
     * @var TimezoneNode
     */
    private $timezone;


    protected function setUp() : void
    {
        parent::setUp();

        $this->identifier  = new IdentifierNode('foo');
        $this->string      = new StringNode('bar');
        $this->boolean     = new BooleanNode(true);
        $this->number      = new NumberNode(42);
        $this->date        = new DateNode(new DateTimeImmutable('2000-01-01 00:00:00'));
        $this->timezone    = new TimezoneNode(new DateTimeZone('Europe/London'));
        $this->stringList  = new LiteralNodeList($this->string, new StringNode('baz'));
        $this->equal       = ComparisonNode::equals($this->identifier, $this->string);
        $this->gt          = ComparisonNode::greaterThan($this->identifier, $this->string);
        $this->gte         = ComparisonNode::greaterThanOrEqualTo($this->identifier, $this->string);
        $this->lt          = ComparisonNode::lessThan($this->identifier, $this->string);
        $this->lte         = ComparisonNode::lessThanOrEqualTo($this->identifier, $this->string);
        $this->in          = ComparisonNode::in($this->identifier, new LiteralNodeList($this->string));
        $this->matches     = ComparisonNode::matches($this->identifier, $this->string);
        $this->containsAny = ComparisonNode::containsAny($this->identifier, $this->stringList);
        $this->containsAll = ComparisonNode::containsAll($this->identifier, $this->stringList);
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
        static::assertFalse($this->containsAny->isIn());
        static::assertFalse($this->containsAll->isIn());
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
        static::assertFalse($this->containsAny->isEquals());
        static::assertFalse($this->containsAll->isEquals());
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
        static::assertFalse($this->containsAny->isGreaterThan());
        static::assertFalse($this->containsAll->isGreaterThan());
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
        static::assertFalse($this->containsAny->isGreaterThanOrEqualTo());
        static::assertFalse($this->containsAll->isGreaterThanOrEqualTo());
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
        static::assertFalse($this->containsAny->isLessThan());
        static::assertFalse($this->containsAll->isLessThan());
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
        static::assertFalse($this->containsAny->isLessThanOrEqualTo());
        static::assertFalse($this->containsAll->isLessThanOrEqualTo());
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
        static::assertFalse($this->containsAny->isMatches());
        static::assertFalse($this->containsAll->isMatches());
    }


    public function testCanDetermineIfComparisonTypeIsContains() : void
    {
        static::assertTrue($this->containsAny->isContains());
        static::assertTrue($this->containsAll->isContains());
        static::assertFalse($this->matches->isContains());
        static::assertFalse($this->equal->isContains());
        static::assertFalse($this->in->isContains());
        static::assertFalse($this->lt->isContains());
        static::assertFalse($this->lte->isContains());
        static::assertFalse($this->gt->isContains());
        static::assertFalse($this->gte->isContains());
    }


    public function testCanDetermineIfComparisonTypeIsContainsAny() : void
    {
        static::assertTrue($this->containsAny->isContainsAny());
        static::assertFalse($this->containsAll->isContainsAny());
        static::assertFalse($this->matches->isContainsAny());
        static::assertFalse($this->equal->isContainsAny());
        static::assertFalse($this->in->isContainsAny());
        static::assertFalse($this->lt->isContainsAny());
        static::assertFalse($this->lte->isContainsAny());
        static::assertFalse($this->gt->isContainsAny());
        static::assertFalse($this->gte->isContainsAny());
    }


    public function testCanDetermineIfComparisonTypeIsContainsAll() : void
    {
        static::assertTrue($this->containsAll->isContainsAll());
        static::assertFalse($this->containsAny->isContainsAll());
        static::assertFalse($this->matches->isContainsAll());
        static::assertFalse($this->equal->isContainsAll());
        static::assertFalse($this->in->isContainsAll());
        static::assertFalse($this->lt->isContainsAll());
        static::assertFalse($this->lte->isContainsAll());
        static::assertFalse($this->gt->isContainsAll());
        static::assertFalse($this->gte->isContainsAll());
    }


    public function testCanDetermineIfValueIsString() : void
    {
        static::assertTrue(ComparisonNode::equals($this->identifier, $this->string)->isValueString());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->boolean)->isValueString());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->number)->isValueString());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->date)->isValueString());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->timezone)->isValueString());
    }


    public function testCanDetermineIfValueIsBoolean() : void
    {
        static::assertTrue(ComparisonNode::equals($this->identifier, $this->boolean)->isValueBoolean());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->string)->isValueBoolean());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->number)->isValueBoolean());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->date)->isValueBoolean());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->timezone)->isValueBoolean());
    }


    public function testCanDetermineIfValueIsNumber() : void
    {
        static::assertTrue(ComparisonNode::equals($this->identifier, $this->number)->isValueNumber());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->string)->isValueNumber());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->boolean)->isValueNumber());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->date)->isValueNumber());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->timezone)->isValueNumber());
    }


    public function testCanDetermineIfValueIsDate() : void
    {
        static::assertTrue(ComparisonNode::equals($this->identifier, $this->date)->isValueDate());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->number)->isValueDate());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->string)->isValueDate());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->boolean)->isValueDate());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->timezone)->isValueDate());
    }


    public function testCanDetermineIfValueIsTimezone() : void
    {
        static::assertTrue(ComparisonNode::equals($this->identifier, $this->timezone)->isValueTimezone());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->date)->isValueTimezone());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->number)->isValueTimezone());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->string)->isValueTimezone());
        static::assertFalse(ComparisonNode::equals($this->identifier, $this->boolean)->isValueTimezone());
    }
}
