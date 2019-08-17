<?php

namespace Krixon\Rules\Compiler;

use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Specification\Specification;

class DelegatingCompiler extends BaseCompiler
{
    /**
     * @var \SplPriorityQueue|SpecificationGenerator[]
     */
    private $generators;


    public function __construct(SpecificationGenerator ...$generators)
    {
        $this->generators = new \SplPriorityQueue();

        foreach ($generators as $generator) {
            $this->register($generator);
        }
    }


    public function register(SpecificationGenerator $generator, $priority = 0) : void
    {
        $this->generators->insert($generator, $priority);
    }


    protected function generate(ComparisonNode $comparison) : Specification
    {
        // The SplPriorityQueue is cloned because iteration removes elements (it's actually a max heap).
        foreach (clone $this->generators as $generator) {
            try {
                $result = $generator->attempt($comparison);
            } catch (CompilerError $exception) {
                // Squash and try the next generator.
                continue;
            }

            if ($result instanceof Specification) {
                return $result;
            }
        }

        throw new CompilerError('No generator was able to produce a Specification from AST.');
    }
}