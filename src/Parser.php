<?php

namespace Krixon\Rules;

interface Parser
{
    public function parse(string $expression) : Ast\Node;
}
