<?php

namespace Krixon\Rules\Ast;

use const DATE_ATOM;

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
        $left  = $node->left();
        $right = $node->right();

        // Simplify expressions like `foo >= 10 and foo <= 20` to `foo between 10 and 20`.
        if ($node->isAnd()
            && $left instanceof ComparisonNode
            && $right instanceof ComparisonNode
            && $left->isGreaterThanOrEqualTo()
            && $right->isLessThanOrEqualTo()) {

            $left->identifier()->accept($this);

            $this->buffer .= ' between ';

            $left->value()->accept($this);

            $this->buffer .= ' and ';

            $right->value()->accept($this);

            return;
        }

        $this->buffer .= '(';

        $left->accept($this);

        switch (true) {
            case $node->isAnd():
                $this->buffer .= ' and ';
                break;
            case $node->isOr():
                $this->buffer .= ' or ';
                break;
            case $node->isXor():
                $this->buffer .= ' xor ';
                break;
        }

        $right->accept($this);

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
        } elseif ($node->isContainsAll()) {
            $this->buffer .= ' contains all of ';
        } elseif ($node->isContainsAny()) {
            $this->buffer .= ' contains any of ';
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


    public function visitDate(DateNode $node) : void
    {
        $date     = $node->value();
        $timezone = $date->getTimezone()->getName();

        $this->buffer .= 'date:"' . $date->format(DATE_ATOM) . '"';

        if ($timezone !== date_default_timezone_get()) {
            $this->buffer .= ' in "' . $timezone . '"';
        }
    }


    public function visitTimezone(TimezoneNode $node) : void
    {
        $this->buffer .= 'timezone:"' . $node->value()->getName() . '"';
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
