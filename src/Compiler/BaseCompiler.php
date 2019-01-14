<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Ast;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification as Spec;

abstract class BaseCompiler implements Compiler, Ast\Visitor
{
    use Ast\VisitsAst;

    /**
     * @var SpecificationStack
     */
    private $specifications;


    public function compile(Ast\Node $node) : Spec\Specification
    {
        $this->specifications = new SpecificationStack();

        $node->accept($this);

        return $this->specifications->pop();
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
        $this->specifications->push($this->generate($node));
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
}
