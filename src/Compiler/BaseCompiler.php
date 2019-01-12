<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Ast;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification as Spec;

abstract class BaseCompiler implements Compiler, Ast\Visitor
{
    /**
     * @var SpecificationStack
     */
    private $specifications;

    /**
     * @var IdentifierNodeStack
     */
    private $identifiers;


    public function compile(Ast\Node $node) : Spec\Specification
    {
        $this->specifications = new SpecificationStack();
        $this->identifiers    = new IdentifierNodeStack();

        $node->accept($this);

        return $this->specifications->pop();
    }


    public function visitIdentifier(Ast\IdentifierNode $node) : void
    {
        $this->identifiers->push($node);
    }


    /**
     * @throws CompilerError
     */
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


    /**
     * @throws CompilerError
     */
    public function visitLogical(Ast\LogicalNode $node) : void
    {
        $node->right()->accept($this);
        $node->left()->accept($this);

        $args = [];

        // Are any of these args a LogicalNode of the same type? If so we can flatten the specification.
        // For example, given ((a or b) or c), convert to (a or b or c).
        foreach ([$this->specifications->pop(), $this->specifications->pop()] as $arg) {
            if ($arg instanceof Spec\Composite && $node->isAnd() === $arg->isAnd()) {
                $args = array_merge($args, $arg->children());
            } else {
                $args[] = $arg;
            }
        }

        if ($node->isAnd()) {
            $this->specifications->push(Spec\Composite::and(...$args));
        } else {
            $this->specifications->push(Spec\Composite::or(...$args));
        }
    }


    /**
     * @throws CompilerError
     */
    public function visitComparison(Ast\ComparisonNode $node) : void
    {
        $node->left()->accept($this);
        $node->right()->accept($this);

        $this->identifiers->pop();

        // The right hand node has now been compiled into a Specification. Should it be negated?
        if ($node->isNotEqual()) {
            $this->specifications->push(new Spec\Not($this->specifications->pop()));
        }
    }


    /**
     * @throws CompilerError
     */
    public function visitString(Ast\StringNode $node) : void
    {
        $this->visitLiteral($node);
    }


    /**
     * @throws CompilerError
     */
    public function visitNumber(Ast\NumberNode $node) : void
    {
        $this->visitLiteral($node);
    }


    abstract protected function literal(Ast\IdentifierNode $identifier, Ast\LiteralNode $node) : Spec\Specification;


    /**
     * @throws CompilerError
     */
    private function visitLiteral(Ast\LiteralNode $node) : void
    {
        $identifier    = $this->identifiers->top();
        $specification = $this->literal($identifier, $node);

        $this->specifications->push($specification);
    }
}
