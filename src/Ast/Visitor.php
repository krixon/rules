<?php

namespace Krixon\Rules\Ast;

interface Visitor
{
    public function visitIdentifier(IdentifierNode $node) : void;

    public function visitNodeList(NodeList $node) : void;

    public function visitLogical(LogicalNode $node) : void;

    public function visitComparison(ComparisonNode $node) : void;

    public function visitString(StringNode $node) : void;

    public function visitNumber(NumberNode $node) : void;

    public function visitBoolean(BooleanNode $node);
}
