<?php

namespace Krixon\Rules;

use Krixon\Rules\Exception\SyntaxError;

interface Parser
{
    /**
     * @throws SyntaxError
     */
    public function parse(string $expression) : Ast\Node;
}
