<?php

namespace Krixon\Rules\Compiler;

class Options
{
    private $convertInToLogicalOr = true;


    public static function default() : self
    {
        return new self;
    }


    /**
     * Prevents the compiler from automatically converting a comparison using the `in` operator into a `Composite`
     * specification of type logical OR. Each element in the list is converted into a child specification using the `is`
     * operator.
     *
     * For example, given the expression `foo in [42, 100, 666]`, a `Composite` specification with 3 children will be
     * produced. Each child will be whatever specification is generated for the expression `foo is <element>`. In
     * this example, the result is a specification equivalent to `foo is 42 or foo is 100 or foo is 666`.
     *
     * If you would like to handle lists yourself within a single specification, perhaps because the evaluation can
     * be handled in a more efficient manner, use this method to disable the default behaviour. The compiler will
     * attempt to generate a specification for `in` comparisons as normal.
     */
    public function disableInToLogicalOrConversion() : self
    {
        $this->convertInToLogicalOr = false;

        return $this;
    }


    /**
     * See the documentation for disableInToLogicalOrConversion() for more information.
     */
    public function enableInToLogicalOrConversion() : self
    {
        $this->convertInToLogicalOr = true;

        return $this;
    }


    /**
     * See the documentation for disableInToLogicalOrConversion() for more information.
     */
    public function isInToLogicalOrConversionEnabled() : bool
    {
        return $this->convertInToLogicalOr;
    }
}