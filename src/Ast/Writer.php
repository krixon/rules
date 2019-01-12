<?php

namespace Krixon\Rules\Ast;

class Writer implements Visitor
{
    private $buffer;


    public function write(Node $node) : string
    {
        $this->buffer = '';

        $node->accept($this);

        return trim($this->buffer);
    }


    public function visitIdentifier(IdentifierNode $node) : void
    {
        $this->buffer .= $node->fullName();
    }


    public function visitNodeList(NodeList $node) : void
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
        $node->left()->accept($this);

        if ($node->isEqual()) {
            $this->buffer .= ' is ';
        } elseif ($node->isNotEqual()) {
            $this->buffer .= ' not ';
        } else {
            $this->buffer .= ' in ';
        }

        $node->right()->accept($this);
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