<?php

declare(strict_types=1);

namespace Krixon\Rules\Specification;

use DateTimeInterface;
use DateTimeZone;
use Krixon\Rules\Operator;
use Krixon\Rules\Specification\Exception\UnsupportedOperator;
use Krixon\Rules\Specification\Exception\UnsupportedValue;
use function is_array;
use function is_bool;
use function is_iterable;
use function is_numeric;
use function is_string;
use function iterator_to_array;

class Contains implements Specification
{
    private $candidates;
    private $operator;


    public function __construct(array $candidates, ?Operator $operator = null)
    {
        $operator = $operator ?? Operator::containsAny();

        if (!$this->supportsOperator($operator)) {
            throw new UnsupportedOperator($this, $operator);
        }

        if (!$this->supportsValue($candidates, $expected)) {
            throw new UnsupportedValue($this, $expected);
        }

        $this->candidates = $candidates;
        $this->operator   = $operator;
    }


    public function isSatisfiedBy($value) : bool
    {
        if (empty($this->candidates)) {
            return false;
        }

        $value = $this->extract($value);
        $any   = $this->operator->isContainsAny();

        foreach ($this->candidates as $candidate) {
            $contains = $this->contains($value, $candidate);

            if ($contains && $any) {
                // We're looking for any candidate and this one is present.
                return true;
            }

            if (!$contains && !$any) {
                // We're looking for all candidate and this one is missing.
                return false;
            }
        }

        // We're looking for any candidate and didn't find one, or
        // We're looking for all candidates and found them all.
        return !$any;
    }


    /**
     * Determine if the set of values contains a candidate.
     *
     * @param mixed[] $values
     * @param mixed $candidate
     */
    protected function contains(array $values, $candidate) : bool
    {
        $specification = $this->createSpecificationForCandidate($candidate);

        if (!$specification) {
            // @codeCoverageIgnoreStart
            // The constructor has validated the types of all candidates, so the only way this can be
            // reached is if this class is extended such that the validation does not cover all cases.
            return false;
            // @codeCoverageIgnoreEnd
        }

        foreach ($values as $value) {
            if ($specification->isSatisfiedBy($value)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Attempt to create a specification which can be used with a single candidate.
     *
     * If the specification is satisfied by the candidate, then the candidate is deemed to be contained.
     *
     * If no specification is returned, the candidate is deemed to be invalid and thus not contained.
     *
     * @param mixed $candidate
     */
    protected function createSpecificationForCandidate($candidate) : ?Specification
    {
        switch (true) {
            case is_string($candidate):
                return new StringMatches($candidate);
            case is_numeric($candidate):
                return new NumberMatches($candidate);
            case is_bool($candidate):
                return new BooleanMatches($candidate);
            case $candidate instanceof DateTimeInterface:
                return new DateMatches($candidate);
            case $candidate instanceof DateTimeZone:
                return new TimezoneMatches($candidate);
        }

        // @codeCoverageIgnoreStart
        // The constructor has already validated the candidates to ensure they are supported, so there is no way
        // to reach this point in a bug-free implementation.
        return null;
        // @codeCoverageIgnoreEnd
    }


    /**
     * Extract the value to test from the input passed to isSatisfiedBy().
     *
     * By default, this returns the input itself if it is an array, or if the value is iterable, an array of the
     * iterable values. Otherwise this input is returned as a single-element array - i.e. the isSatisfied check passes
     * if the value itself matches a candidate.
     *
     * This method can be overridden to perform custom extraction logic if required.
     *
     * @param mixed $value
     * @return mixed[]
     *
     * @throws UnsupportedValue
     */
    protected function extract($value) : array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_iterable($value)) {
            return iterator_to_array($value);
        }

        return [$value];
    }


    protected function supportsOperator(Operator $operator) : bool
    {
        return $operator->isContains();
    }


    protected function supportsValue(array $value, &$types) : bool
    {
        $types = 'string | number | boolean | DateTimeInterface | DateTimeZone';

        foreach ($value as $candidate) {
            if (
                !is_string($candidate)
                && !is_numeric($candidate)
                && !is_bool($candidate)
                && !$candidate instanceof DateTimeInterface
                && !$candidate instanceof DateTimeZone
            ) {
                return false;
            }
        }

        return true;
    }
}
