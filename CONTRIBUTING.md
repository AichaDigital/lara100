# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/aichadigital/lara100).

## Pull Requests

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

## Running Tests

```bash
composer test
```

## Code Style

We use Laravel Pint for code style. Before committing, run:

```bash
composer format
```

To check code style without fixing:

```bash
vendor/bin/pint --test
```

## Static Analysis

We use PHPStan for static analysis:

```bash
composer phpstan
```

## Testing

We use Pest PHP for testing:

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/pest tests/Unit/Casts/Base100Test.php

# Run with coverage
composer test-coverage

# Run in parallel
composer test-parallel
```

**Happy coding**!

