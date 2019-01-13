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

- PHP 7.1+

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

# Usage

The main task involved in using this library is implementing `BaseCompiler::literal()`. This method has the following
signature:

```php
public function literal(IdentifierNode $identifier, LiteralNode $node) : Specification
```

Its job is to take an identifier and a corresponding literal value and to return a Specification object.

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

You can define a rule for this Specification as `email is "karl.rixon@gmail.com"`. When compiling this rule, the
`BaseCompiler` will invoke `literal()` with an `IdentifierNode` containing the value `email` and a `StringNode`
containing the value `karl.rixon@gmail.com`. The `literal()` method might be implemented as follows:

```php
public function literal(IdentifierNode $identifier, LiteralNode $node) : Specification
{
    switch (strtolower($identifier->fullName())) {
        case 'email':
            return new EmailAddressMatches($node->value());
        // case ...
    }

    throw new CompilerError(sprintf("Unknown identifier '%s'.", $identifier->fullName()));
}
```