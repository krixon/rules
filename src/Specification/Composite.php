<?php

namespace Krixon\Rules\Specification;

class Composite implements Specification
{
    private const AND = 'AND';
    private const OR  = 'OR';
    private const XOR = 'XOR';

    private $children;
    private $type;


    private function __construct(string $type, Specification ...$children)
    {
        $this->type     = $type;
        $this->children = $children;
    }


    public static function and(Specification ...$children) : self
    {
        return new static(self::AND, ...$children);
    }


    public static function or(Specification ...$children) : self
    {
        return new static(self::OR, ...$children);
    }


    public static function xor(Specification ...$children) : self
    {
        return new static(self::XOR, ...$children);
    }


    public function isSatisfiedBy($value) : bool
    {
        if (empty($this->children)) {
            return false;
        }

        $numTrue = 0;

        foreach ($this->children as $child) {
            if ($this->type === self::OR) {
                // OR: We can return as soon as any child is true.
                if ($child->isSatisfiedBy($value)) {
                    return true;
                }
            } elseif ($this->type === self::AND) {
                // AND: We can return as soon as any child is false.
                if (!$child->isSatisfiedBy($value)) {
                    return false;
                }
            } else {
                // XOR: We can return as soon as there is more than one true child.
                if ($child->isSatisfiedBy($value) && ++$numTrue > 1) {
                    return false;
                }
            }
        }

        // If we are XOR joined, return true only if there has been exactly one matching child.
        // If we are OR joined, there have been no matching children seen so return false.
        // If we are AND joined, there have been only matching children seen so return true.
        return ($this->type === self::XOR && $numTrue === 1) || $this->type === static::AND;
    }


    public function isAnd() : bool
    {
        return $this->type === self::AND;
    }


    public function isOr() : bool
    {
        return $this->type === self::OR;
    }


    public function isXor() : bool
    {
        return $this->type === self::XOR;
    }


    /**
     * @return Specification[]
     */
    public function children() : array
    {
        return $this->children;
    }
}
