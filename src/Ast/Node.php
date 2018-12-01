<?php

namespace Krixon\Rules\Ast;

abstract class Node
{
    abstract public function accept(Visitor $visitor) : void;
}
