<?php

namespace Krixon\Rules\Ast;

/**
 * Trait which provides noop implementations of all Visitor methods.
 *
 * This can be useful when a visitor only needs to implement a subset of the Visitor API.
 */
trait VisitsAst
{
    // @codeCoverageIgnoreStart
    // Cannot test an empty implementation, but XDebug treats these empty methods as missed lines.

    public function visitIdentifier(IdentifierNode $node) : void
    {

    }


    public function visitLiteralNodeList(LiteralNodeList $node) : void
    {

    }


    public function visitLogical(LogicalNode $node) : void
    {

    }


    public function visitComparison(ComparisonNode $node) : void
    {

    }


    public function visitNegation(NegationNode $node) : void
    {

    }


    public function visitString(StringNode $node) : void
    {

    }


    public function visitNumber(NumberNode $node) : void
    {

    }


    public function visitBoolean(BooleanNode $node)
    {

    }
    // @codeCoverageIgnoreEnd
}