# Contributing

Contributions to this repository are welcomed. If your change is large or complex, please first
consider discussing the change you wish to make via issue, email, or any other method with the
owners of this repository. 

## Pull Request Process

1. Ensure all changes are covered by tests and that all tests pass.
1. Update the [syntax documentation](./docs/syntax.md) with details of any changes to the language.
1. You may merge the Pull Request once you have the sign-off of from a project owner.

## Installing Dependencies

Dependencies are managed via composer. To install them, run:

```bash
composer install
```

If you have docker-compose installed, you install dependencies via a container:

```bash
docker-compose run --rm lib composer install
```

## Running Tests

Tests are run via PHPUnit. Assuming you have PHP installed, you can execute tests by running:

```bash
vendor/bin/phpunit
```

If you have docker-compose installed, tests can be run via a container:

```bash
docker-compose run --rm lib phpunit
```