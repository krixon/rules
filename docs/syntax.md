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

### `between`

Determines if an identifier falls within a range.

Two forms of this comparison are supported; a simple inclusive range, and a flexible range using interval notation.

#### Simple Form

This form of `between` implies an inclusive range, and is syntactic sugar for 
`[identifier] >= [value] and [identifier] <= [value].

- `foo between 10 and 20`
- `foo between "a" and "z"`
- `foo between date:"2019-01-01 00:00:00" and date:"2019-01-02 00:00:00"`

Care should be taken when using this form of `between` with dates. Because the range is inclusive, a rule such as
`foo between date:"2019-01-01 00:00:00" and date:"2019-01-02 00:00:00"` will evaluate true for `2019-01-02 00:00:00`
(i.e. the first second of next day is included) which is probably not what is desired. Instead, consider using the
interval notation form as described below.

#### Interval Notation Form

This form of `between` uses [interval notation](https://en.wikipedia.org/wiki/Interval_%28mathematics%29) to define
the range. It is useful when you need control over whether the start and end of the range is included or not.

- `foo between [1,5] // Closed, includes 1, 2, 3, 4, 5.`
- `foo between (1,5) // Open, includes 2, 3, 4.`
- `foo between (1,5] // Half-open, includes 2, 3, 4, 5.`
- `foo between [1,5) // Half-open, includes 1, 2, 3, 4.`
- `foo between ["a","e") // Half-open, includes b, c, d, e.`
- `foo between [date:"2019-01-01 00:00:00", date:"2019-01-02 00:00:00") // Half-open, includes 2019-01-01 00:00:00 but not 2019-01-02 00:00:00.`

### `matches`

Determines if an identifier matches a regular expression.

`name matches "/^(arnold|dave|kryten)/i"`

## Logical Operators

The language supports the following logical operators.

### `and`

Combines two comparisons such that both must be true for the rule to pass.

`foo is 10 and bar is 10`

#### Truth Table

| A | B | Q |
|---|---|---|
| 0 | 0 | 0 |
| 0 | 1 | 0 |
| 1 | 0 | 0 |
| 1 | 1 | 1 |

### `or`

Combines two comparisons such that either or both must be true for the rule to pass.

`foo is 10 or bar is 10`

#### Truth Table

| A | B | Q |
|---|---|---|
| 0 | 0 | 0 |
| 0 | 1 | 1 |
| 1 | 0 | 1 |
| 1 | 1 | 1 |

### `xor`

Combines two comparisons such that one or the other must be true for the rule to pass, but not neither or both.

`foo is 10 xor bar is 10`

#### Truth Table

| A | B | Q |
|---|---|---|
| 0 | 0 | 0 |
| 0 | 1 | 1 |
| 1 | 0 | 1 |
| 1 | 1 | 0 |

## Groups

When using multiple logical operators, it is sometimes helpful (or even necessary) to group sets of comparisons
together. Consider the following example:

`age is 30 or age is 40 and name is "Arnold"`

This rule could be interpreted in a couple of ways:

- The respondent is a 30 year old with any name, or is a 40 year old named Arnold.
- The respondent is either 30 or 40 years old, but regardless must be named Arnold.

Logical operators are left associative, so the rule will be interpreted as option 2; the respondent is either 30 or
40 years old, but regardless must be named Arnold. To resolve this ambiguity, sets of comparisons can be grouped with
parentheses. If option 1 was intended, the rule can be rewritten like this:

`age is 30 or (age is 40 and name is "Arnold")`

Even if the rule would be interpreted correctly without explicit groups, it is recommended to use them anyway for
clarity.

Groups can be nested within each other to form complex rules with specific semantics:

`age is 30 or (age is 40 and (name is "Arnold" or name is "Dave" or name is "Kryten"))`

## Literal Values

Literal values are used to compare against identifiers. The language supports a few types of literal value.

### String

Strings are enclosed within double quotes (`"`). They can contain any UTF-8 encoded character.

```
name is "Arnold"
```

If you need to include a double quote character within the string, it must be escaped with a backslash (`\`):

```
name is "Dave \"Bum\" Lister"
```

### Number

Numbers are always treated as `float`s.

```
foo > 1 and foo not 3.14
```

### Boolean

Booleans are supported using `true` and `false`. No other literal type is interpreted as a boolean, so `foo is 1` will
not pass if foo is boolean `true` for example.

```
foo is true and bar is false
```

### Date

Dates are strings which have the `date:` type hint.

```
foo is date:"2019-01-01"
```

Any valid PHP [date and time format](http://php.net/manual/en/datetime.formats.php) can be used.

```
foo is date:"2019-01-01 12:30:45"
foo is date:"now"
foo is date:"10 days ago"
// etc
```

It is also possible to specify a timezone in which the date should be interpreted. If no timezone is specified,
the default timezone is used.

```
foo is date:"2019-01-01 12:30:45" in "Europe/London"
```

### Timezone

Timezones are strings which have the `timezone:` type hint.

```
foo is timezone:"Europe/London"
```

## Comments

Comments can be used within rules. Two types of comment are supported, line comments and block comments.

### Line Comments

Line comments are prefixed with `//` can appear on their own line or at the end of a line. Anything after the `//`
is treated as the comment.

```
// this is a comment on its own line
foo is 10    // this is a comment at the end of a line
or bar is 10 // this is another comment at the end of a line
```

### Block Comments

Block comments start with `/*` and end with `*/`. Because they have a specific start and end, they can appear
within lines.

```
/* this is a comment on its own line */
foo is 10
/*
Block comments can span multiple lines
for longer chunks of comment.
*/
or bar /* and appear within a line */ is 10
```