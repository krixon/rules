<?php

namespace Krixon\Rules\Parser;

use Krixon\Rules\Ast;
use Krixon\Rules\Exception\SyntaxError;

interface Parser
{
    /**
     * @throws SyntaxError
     */
    public function parse(string $expression) : Ast\Node;
}
