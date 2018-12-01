<?php

namespace Krixon\Rules\Ast;

class NodeList extends Node implements \Countable
{
    private $nodes;


    public function __construct(Node ...$nodes)
    {
        $this->nodes = $nodes;
    }


    /**
     * @return Node[]
     */
    public function nodes() : array
    {
        return $this->nodes;
    }


    public function count() : int
    {
        return count($this->nodes);
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitNodeList($this);
    }
}
