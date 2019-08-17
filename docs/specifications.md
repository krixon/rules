# Built-in Specifications

Several specifications are provided by the library. These perform basic comparisons against numbers, strings,
booleans, dates and timezones. These can be used as-is, or extended if more custom logic is required. There is no
requirement to use these specifications; they are simply provided as a convenience.

## Generating built-in specifications

Generators are provided for each of the built-in specifications. These can be used with the `DelegatingCompiler`:

```php
$compiler = new DelegatingCompiler(
    new BooleanMatchesGenerator(),
    new NumberMatchesGenerator(),
    new StringMatchesGenerator(),
    // ...
);

$parser = new DefaultParser();

$ast           = $parser->parse('foo is true');
$specification = $compiler->compile($ast); // $specification is a BooleanMatches instance

$ast           = $parser->parse('foo matches "/^arnold/i"');
$specification = $compiler->compile($ast); // $specification is a StringMatches instance
```

Note that sometimes a single comparison expression can be handled by multiple different specifications. For example,
the expression `foo matches "/^europe/"` could be handled by `StringMatches` or `TimezoneMatches` since they both
support the `matches` operator. In this situation, if the built-in generators are used, the first one provided to
the `DelegatingCompiler` (or the one with highest priority if `DelegatingCompiler::register()` is used) will win.

Sometimes this can be avoided by specifying which identifiers are supported by a given generator. If an identifier
is not supported, that generator will skip generation, allowing another to attempt it. All of the built-in generators
support this:

```php
$compiler = new DelegatingCompiler(
    new StringMatchesGenerator('name', 'email'),
    new TimezoneMatchesGenerator('timezone'),
    // ...
);
```

Another way of avoiding this issue is to extend the generator so that the identifier can be examined using
whatever custom logic is required:

```php
class UserMatchesGenerator extends StringMatchesGenerator
{
    public function attempt(ComparisonNode $comparison) : ?Specification
    {
        $identifier = $comparison->identifier();

        // Support all `user.*` identifiers.
        if ($identifer()->name() === 'user' && $identifier->hasSubIdentifier()) {
            return parent::attempt($comparison);
        }

        return null;
    }
}
```

Another useful way to extend the built-in generators is by generating a custom specification. Most built-in
generators define a `generate` method which can be overridden:

```php
class CustomTimezoneMatchesGenerator extends TimezoneMatchesGenerator
{
    protected function generate(DateTimeZone $timezone, Operator $operator) : TimezoneMatches
    {
        return new CustomTimezoneMatches($timezone, $operator);
    }
}
```

Of course you can also write your own generators from scratch which generate either a built-in specification or a
totally custom one; you just need to implement the `SpecificationGenerator` interface and provide an instance
to the `DelegatingCompiler`.

## `BooleanMatches`

The `BooleanMatches` specification is satisfied by a value which is boolean `true` or `false`.

```php
$specification = new BooleanMatches(true);

$specification-isSatisfiedBy(true);  // returns true
$specification-isSatisfiedBy(false); // returns false

$specification = new BooleanMatches(false);

$specification-isSatisfiedBy(true);  // returns false
$specification-isSatisfiedBy(false); // returns true
```

You can extend the `BooleanMatches` specification to accept a custom type from which a boolean value is extracted:

```php
class UserCanLogIn extends BooleanMatches
{
    protected function extract($value) : ?bool
    {
        if (!$value instanceof User) {
            return null;
        }

        return $value->canLogin();
    }
}
```

Note that operators other than `is` and `not` are not supported for boolean literals and will cause a `CompilerError`
to be thrown:

```php
foo > true // CompilerError: Unsupported comparison operator 'GREATER_EQUALS' for identifier 'foo' and operand type 'BOOLEAN'
```

### `BooleanMatchesGenerator`

This generator can be used with the `DelegatingCompiler` to automatically build a default `BooleanMatches`
specification for any boolean `ComparisonNode`:

```php
$compiler = new DelegatingCompiler(
    new BooleanMatchesGenerator(),
    // ...
);

$ast           = (new DefaultParser())->parse('foo is true');
$specification = $compiler->compile($ast); // $specification is a BooleanMatches instance
```

## `StringMatches`

The `StringMatches` specification supports may types of string comparison.

### `is`

Satisfied when the string exactly matches a candidate.

```php
// name is "Arnold Rimmer"
$specification = new StringMatches('Arnold Rimmer');

$specification-isSatisfiedBy("Arnold Rimmer"); // true
$specification-isSatisfiedBy("Dave Lister");   // false
```

### `<`

Satisfied when a string is alphabetically ordered before a candidate.

```php
// name < "Dave Lister"
$specification = new StringMatches('Dave Lister', Operator::lessThan());

$specification-isSatisfiedBy("Arnold Rimmer"); // true
$specification-isSatisfiedBy("Dave Lister");   // false
$specification-isSatisfiedBy("Kryten");        // false
```

### `<=`

Satisfied when a string is alphabetically ordered before or equal to a candidate.

```php
// name <= "Dave Lister"
$specification = new StringMatches('Dave Lister', Operator::lessThanOrEquals());

$specification-isSatisfiedBy("Arnold Rimmer"); // true
$specification-isSatisfiedBy("Dave Lister");   // true
$specification-isSatisfiedBy("Kryten");        // false
```

### `>`

Satisfied when a string is alphabetically ordered after a candidate.

```php
// name > "Dave Lister"
$specification = new StringMatches('Dave Lister', Operator::greaterThan());

$specification-isSatisfiedBy("Arnold Rimmer"); // false
$specification-isSatisfiedBy("Dave Lister");   // false
$specification-isSatisfiedBy("Kryten");        // true
```

### `>=`

Satisfied when a string is alphabetically ordered after or equal to a candidate.

```php
// name >= "Dave Lister"
$specification = new StringMatches('Dave Lister', Operator::greaterThanOrEquals());

$specification-isSatisfiedBy("Arnold Rimmer"); // false
$specification-isSatisfiedBy("Dave Lister");   // true
$specification-isSatisfiedBy("Kryten");        // true
```

You can extend the `StringMatches` specification to accept a custom type from which a string value is extracted:

```php
class UsernameMatches extends StringMatches
{
    protected function extract($value) : ?string
    {
        if (!$value instanceof User) {
            return null;
        }

        return $value->username();
    }
}
```

### `StringMatchesGenerator`

This generator can be used with the `DelegatingCompiler` to automatically build a default `StringMatches`
specification for any valid `ComparisonNode`:

```php
$compiler = new DelegatingCompiler(
    new StringMatchesGenerator(),
    // ...
);

$ast           = (new DefaultParser())->parse('foo is "Arnold Rimmer"');
$specification = $compiler->compile($ast); // $specification is a StringMatches instance
```