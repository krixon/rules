<?php

namespace Krixon\Rules;

use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification as Spec;

abstract class Compiler implements Ast\Visitor
{
    /**
     * @var \SplStack
     */
    private $specifications;

    /**
     * @var \SplStack
     */
    private $identifiers;


    public function compile(Ast\Node $node) : Spec\Specification
    {
        $this->specifications = new \SplStack();
        $this->identifiers    = new \SplStack();

        $node->accept($this);

        $root = $this->specifications->pop();

        if ($this->specifications->count() !== 0) {
            throw new CompilerError('Left over specifications in the stack.');
        }

        if ($this->identifiers->count() !== 0) {
            throw new CompilerError('Left over identifiers in the stack.');
        }

        return $root;
    }


    public function visitIdentifier(Ast\IdentifierNode $node) : void
    {
        $this->identifiers->push($node);
    }


    public function visitNodeList(Ast\NodeList $node) : void
    {
        foreach ($node->nodes() as $child) {
            $child->accept($this);
        }

        $children = [];
        $n        = $node->count();

        while ($n--) {
            $children[] = $this->specifications->pop();
        }

        $children = array_reverse($children);

        $this->specifications->push(Spec\Composite::or(...$children));
    }


    public function visitLogical(Ast\LogicalNode $node) : void
    {
        $node->right()->accept($this);
        $node->left()->accept($this);

        // TODO: If the child of a logical group is a logical group of the same type, flatten it.
        // For example, given ((a or b) or c), convert to (a or b or c).

        $args = [$this->specifications->pop(), $this->specifications->pop()];

        if ($node->isAnd()) {
            $this->specifications->push(Spec\Composite::and(...$args));
        } else {
            $this->specifications->push(Spec\Composite::or(...$args));
        }
    }


    public function visitComparison(Ast\ComparisonNode $node) : void
    {
        $node->left()->accept($this);
        $node->right()->accept($this);

        $this->identifiers->pop();

        if ($node->isNotEqual()) {
            $this->specifications->push(new Spec\Not($this->specifications->pop()));
        }
    }


    public function visitString(Ast\StringNode $node) : void
    {
        $this->visitLiteral($node);
    }


    public function visitNumber(Ast\NumberNode $node) : void
    {
        $this->visitLiteral($node);
    }


    public function visitLiteral(Ast\LiteralNode $node) : void
    {
        /** @var Ast\IdentifierNode $identifier */
        $identifier    = $this->identifiers->top();
        $specification = $this->literal($identifier, $node);

        $this->specifications->push($specification);
    }


    abstract protected function literal(Ast\IdentifierNode $identifier, Ast\LiteralNode $node);
}
