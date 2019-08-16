Rules
=====

[![Build Status](https://travis-ci.org/krixon/rules.svg?branch=master)](https://travis-ci.org/krixon/rules)
[![Coverage Status](https://coveralls.io/repos/github/krixon/rules/badge.svg?branch=master)](https://coveralls.io/github/krixon/rules?branch=master)
[![Code Climate](https://codeclimate.com/github/krixon/rules/badges/gpa.svg)](https://codeclimate.com/github/krixon/rules)
[![Latest Stable Version](https://poser.pugx.org/krixon/rules/v/stable)](https://packagist.org/packages/krixon/rules)
[![Latest Unstable Version](https://poser.pugx.org/krixon/rules/v/unstable)](https://packagist.org/packages/krixon/rules)
[![License](https://poser.pugx.org/krixon/rules/license)](https://packagist.org/packages/krixon/rules)

A simple language for defining and building [Specification Pattern](https://en.wikipedia.org/wiki/Specification_pattern) objects.

# Prerequisites

- PHP 7.2+

# Installation
## Install via composer

To install this library with Composer, run the following command:

```sh
$ composer require krixon/rules
```

You can see this library on [Packagist](https://packagist.org/packages/krixon/rules).

## Install from source

```sh
# HTTP
$ git clone https://github.com/krixon/rules.git
# SSH
$ git clone git@github.com:krixon/rules.git
```

# Supported Syntax

Refer to the [syntax documentation](./docs/syntax.md) for detailed information on the rule syntax.

# Usage

The main task involved in using this library is implementing `BaseCompiler::generate()`. This method has the following
signature:

```php
public function generate(ComparisonNode $comparison) : Specification
```

Its job is to generate a `Specification` object from a `ComparisonNode` AST object.

A `ComparisonNode` consists of an `IdentifierNode` which identifies the data against which the specification should
be checked, and a `LiteralNode` which contains the value to compare against. It also contains information about
the type of comparison (equals, greater than, etc).

For example, imagine you have the following Specification which can be applied to a `User` object:

```php
class EmailAddressMatches implements Specification
{
    private $email;
    
    
    public function __construct(string $email)
    {
        $this->email = $email;
    }
    
    
    public function isSatisfiedBy($value) : bool
    {
        return $value instanceof User && $value->hasEmailAddress($this->email);
    }
}
```

You can define a rule for this Specification as `email is "karl.rixon@gmail.com"`.

In this rule, `email` is an identifier which refers to the user's email address. It is up to you how to interpret a
given identifier. The string value `email` is converted to an `IdentifierNode` AST node during parsing. This node can
be accessed via `ComparisonNode::identifier()`.

The comparison operator is `is`, which means "equals". You can use `ComparisonNode::isEquals()`, 
`ComparisonNode::isLessThan()` etc to determine the comparison type.

Finally, `karl.rixon@gmail.com` is converted into a `StringNode` AST node during parsing. This node can be accessed
via `ComparisonNode::value()`.

Based on the above, the `BaseCompiler::generate()` method might be implemented as follows:

```php
class MyCompiler extends BaseCompiler
{
  public function generate(ComparisonNode $comparison) : Specification
  {
      $identifier = $comparison->identifierFullName();
      
      if (strtolower($indentifier) !== 'email') {
          throw CompilerError::unknownIdentifier($identifier);
      }
      
      if (!$comparison->isEquals()) {
          throw CompilerError::unsupportedComparisonType($comparison->type(), $identifier);
      }
  
      return new EmailAddressMatches($comparison->literalValue());
  }
}
```

## Delegating generation to services

Although extending `BaseCompiler` is convenient in simple cases, it becomes complicated when you have many
specifications to support. In this case, you might want to delegate the generation work to dedicated services.

The `DelegatingCompiler` class is provided for this purpose. To use it, first create a class which implements the
`SpecificationGenerator` interface, which defines a single method:

```php
public function attempt(ComparisonNode $comparison) : ?Specification;
```

This is very similar to `BaseCompiler::generate()`, however returning a `Specification` is optional.

Next, register an instance of your class with the `DelegatingCompiler`:

```php
$generator = new EmailAddressGenerator();
$compiler  = new DelegatingCompiler($generator);
```

When `DelegatingCompiler::compile()` is invoked, the `DelegatingCompiler` will loop through all registered generators
and call `SpecificationGenerator::attempt()` with each `ComparisonNode`.

All `SpecificationGenerator`s provided via the `DelegatingCompiler`'s constructor share the same priority of `0`,
however they can also be registered with an explicit priority:

```php
$generator = new EmailAddressGenerator();
$compiler  = new DelegatingCompiler();

$compiler->register($generator, 100); // Priority of 100.
```

`SpecificationGenerator`s with higher priority are invoked first.

## Negating comparisons

`ComparisonNode` does not expose negated comparisons like `does not equal` and `does not match`. However this is
supported in the language by adding `not` before the comparison operator:

```
email not is "karl.rixon@gmail.com"
```
```
address.county not matches "/(east|west)\s+sussex/i"
```
```
age not > 5
```

You do not need to write any code to handle these cases because the compiler will produce a `Specification` based on
the non-negated comparison and then wrap the result in a `Not` specification which simply inverts the result of
`Specification::isSatisfiedBy` returned by the wrapped `Specification`.

A shorthand syntax for `not is` can also be used by simply omitting the `is`:

```
email not "karl.rixon@gmail.com"
```

# Contributing

Please refer to [CONTRIBUTING.md](./CONTRIBUTING.md)