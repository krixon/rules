Syntax
======

## General Concepts

The basic building block of rules is a comparison, which takes the form `[identifier] [operator] [value]`. An
example of a comparison is `age is 30` or `name is "Arnold"`. More complex rules can be built by combining multiple
comparisons with logical operators such as `and` and `or`. An example of a more complex rule is
`age is 30 or name is "Arnold"`.

## Comparison Operators

### `is`

Determines if an identifier is equal to a value. The synonym `==` can also be used.

### `not`

Determines if an identifier is not equal to a value. The synonym `!=` can also be used.

### `in`

Determines if an identifier is equal to one of a list of values.

### `>`

Determines if an identifier is greater than a value.

### `>=`

Determines if an identifier is greater than or equal to a value.

### `<`

Determines if an identifier is less than a value.

### `<=`

Determines if an identifier is less than or equal to a value.

### `matches`

Determines if an identifier matches a regular expression.

`name matches "/^(arnold|dave|kryten)/i"`

## Logical Operators

The language supports the following logical operators.

In future, `xor` (exclusive or) will also be supported. See [issue #7](https://github.com/krixon/rules/issues/7).

### `and`

Combines two comparisons such that both must be true for the rule to pass.

### `or`

Combines two comparisons such that either or both must be true for the rule to pass.

## Groups

When using multiple logical operators, it is sometimes helpful (or even necessary) to group sets of comparisons
together. Consider the following example:

`age is 30 or age is 40 and name is "Arnold"`

This rule could be interpreted in a couple of ways:

The respondent is a 30 year old with any name, or is a 40 year old named Arnold.
The respondent is either 30 or 40 years, but regardless must be named Arnold.

Logical operators are left associative, so the rule will be interpreted as option 2; the respondent is either 30 or
40 years, but regardless must be named Arnold. To resolve this ambiguity, sets of comparisons can be grouped with
parentheses. If option 1 was intended, the rule can be rewritten like this:

`age is 30 or (age is 40 and name is "Arnold")`

Even if the rule would be interpreted correctly without explicit groups, it is recommended to use them anyway for
clarity.

Groups can be nested within each other to form complex rules with specific semantics:

`age is 30 or (age is 40 and (name is "Arnold" or name is "Dave" or name is "Kryten"))`