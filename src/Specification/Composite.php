<?php

namespace Krixon\Rules\Specification;

class Composite implements Specification
{
    private const AND = 0;
    private const OR  = 1;

    private $children;
    private $type;


    private function __construct(int $type, Specification ...$children)
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


    public function isSatisfiedBy($value) : bool
    {
        if (empty($this->children)) {
            return false;
        }

        foreach ($this->children as $child) {
            if ($this->type === static::OR) {
                // We can return as soon as any child is true.
                if ($child->isSatisfiedBy($value)) {
                    return true;
                }
            } else {
                // We can return as soon as any child is false.
                if (!$child->isSatisfiedBy($value)) {
                    return false;
                }
            }
        }

        // If we are OR joined, there have been no matching children seen so return false.
        // If we are AND joined, there have been only matching children seen so return true.
        return $this->type === static::AND;
    }
}
