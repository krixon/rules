<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Ast;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification as Spec;

abstract class BaseCompiler implements Compiler, Ast\Visitor
{
    use Ast\VisitsAst;

    /**
     * @var SpecificationStack
     */
    private $specifications;

    /**
     * @var Options
     */
    private $options;


    public function compile(Ast\Node $node, ?Options $options = null) : Spec\Specification
    {
        $this->specifications = new SpecificationStack();
        $this->options        = $options ?? Options::default();

        $node->accept($this);

        $result = $this->specifications->pop();

        $this->specifications = null;
        $this->options        = null;

        return $result;
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
            if ($arg instanceof Spec\Composite && $this->isLogicallyCompatible($node, $arg)) {
                $args = array_merge($args, $arg->children());
            } else {
                $args[] = $arg;
            }
        }

        switch (true) {
            case $node->isAnd():
                $this->specifications->push(Spec\Composite::and(...$args));
                break;
            case $node->isOr():
                $this->specifications->push(Spec\Composite::or(...$args));
                break;
            case $node->isXor():
                $this->specifications->push(Spec\Composite::xor(...$args));
                break;
            default:
                // @codeCoverageIgnoreStart
                // This should not be possible to reach in a bug-free implementation, but is thrown here to
                // help prevent future bugs if a new composite type is implemented without a corresponding branch
                // in this case statement.
                throw CompilerError::unsupportedLogicalOperation();
                // @codeCoverageIgnoreEnd
        }
    }


    /**
     * @throws CompilerError
     */
    public function visitComparison(Ast\ComparisonNode $node) : void
    {
        // If the operator is "in", build a "logical or" specification. This avoids the need for specifications
        // to explicitly handle list values. However sometimes it might be preferable for a single custom
        // specification to handle the list (e.g. for performance), so this behaviour is optional.

        if (!$this->options->isInToLogicalOrConversionEnabled() || !$node->isIn() || !$node->isValueList()) {
            $this->specifications->push($this->generate($node));
            return;
        }

        /** @var Ast\LiteralNodeList $list */
        $list     = $node->value();
        $children = [];

        foreach ($list->nodes() as $value) {
            $equals     = new Ast\ComparisonNode($node->identifier(), Operator::equals(), $value);
            $children[] = $this->generate($equals);
        }

        $this->specifications->push(Spec\Composite::or(...$children));
    }


    /**
     * @throws CompilerError
     */
    public function visitNegation(Ast\NegationNode $node) : void
    {
        $node->negated()->accept($this);

        $this->specifications->push(new Spec\Not($this->specifications->pop()));
    }


    /**
     * @throws CompilerError If a Specification cannot be generated.
     */
    abstract protected function generate(Ast\ComparisonNode $comparison) : Spec\Specification;


    /**
     * @throws CompilerError
     */
    private function isLogicallyCompatible(Ast\LogicalNode $node, Spec\Composite $specification) : bool
    {
        if ($node->isAnd()) {
            return $specification->isAnd();
        }

        if ($node->isOr()) {
            return $specification->isOr();
        }

        if ($node->isXor()) {
            return $specification->isXor();
        }

        // @codeCoverageIgnoreStart
        // It is impossible to reach this point in a bug-free implementation.
        throw CompilerError::unsupportedLogicalOperation();
        // @codeCoverageIgnoreEnd
    }
}
