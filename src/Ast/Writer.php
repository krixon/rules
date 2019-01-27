<?php

namespace Krixon\Rules\Ast;

class Writer implements Visitor
{
    private $buffer;
    private $negating;


    public function write(Node $node) : string
    {
        $this->buffer   = '';
        $this->negating = false;

        $node->accept($this);

        return trim($this->buffer);
    }


    public function visitIdentifier(IdentifierNode $node) : void
    {
        $this->buffer .= $node->fullName();
    }


    public function visitLiteralNodeList(LiteralNodeList $node) : void
    {
        $this->buffer .= '[';

        foreach ($node->nodes() as $child) {
            $child->accept($this);
            $this->buffer .= ', ';
        }

        $this->buffer = trim($this->buffer, ', ') . ']';
    }


    public function visitLogical(LogicalNode $node) : void
    {
        $this->buffer .= '(';

        $node->left()->accept($this);

        $this->buffer .= $node->isAnd() ? ' and ' : ' or ';

        $node->right()->accept($this);

        $this->buffer .= ')';
    }


    public function visitComparison(ComparisonNode $node) : void
    {
        $node->identifier()->accept($this);

        $negated = $this->negating;

        if ($this->negating) {
            $this->buffer  .= ' not';
            $this->negating = false;
        }

        if ($node->isEquals()) {
            $this->buffer .= $negated ? ' ' : ' is ';
        } elseif ($node->isMatches()) {
            $this->buffer .= ' matches ';
        } elseif ($node->isIn()) {
            $this->buffer .= ' in ';
        } elseif ($node->isGreaterThan()) {
            $this->buffer .= ' > ';
        } elseif ($node->isGreaterThanOrEqualTo()) {
            $this->buffer .= ' >= ';
        } elseif ($node->isLessThan()) {
            $this->buffer .= ' < ';
        } elseif ($node->isLessThanOrEqualTo()) {
            $this->buffer .= ' <= ';
        }

        $node->value()->accept($this);
    }


    public function visitNegation(NegationNode $node) : void
    {
        $this->negating = true;

        $node->negated()->accept($this);
    }


    public function visitString(StringNode $node) : void
    {
        $this->buffer .= '"' . $node->value() . '"';
    }


    public function visitNumber(NumberNode $node) : void
    {
        $this->buffer .= $node->value();
    }


    public function visitBoolean(BooleanNode $node)
    {
        $this->buffer .= $node->value() ? 'true' : 'false';
    }
}