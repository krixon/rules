<?php

namespace Krixon\Rules\Ast;

class LiteralNodeList implements LiteralNode
{
    private $nodes;


    public function __construct(LiteralNode ...$nodes)
    {
        $this->nodes = $nodes;
    }


    public static function type() : string
    {
        return 'list';
    }


    public function value()
    {
        return array_map(
            function (LiteralNode $node) {
                return $node->value();
            },
            $this->nodes
        );
    }


    /**
     * @return LiteralNode[]
     */
    public function nodes() : array
    {
        return $this->nodes;
    }


    public function accept(Visitor $visitor) : void
    {
        $visitor->visitLiteralNodeList($this);
    }
}
