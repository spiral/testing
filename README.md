# Spiral Framework testing SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/testing.svg?style=flat-square)](https://packagist.org/packages/spiral/testing)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral/testing.svg?style=flat-square)](https://packagist.org/packages/spiral/testing)

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.4+
- Spiral framework 3.16+
- [Testo](https://php-testo.github.io) — the test runner the `TestCase` is built on

Documentation on how to install and use the package can be found on the official documentation
page - [Testing — Getting Started](https://spiral.dev/docs/testing-start)

## Running tests with Testo

`Spiral\Testing\TestCase` has no PHPUnit parent: tests run on Testo. Declare the suites in `testo.php`
at the project root and run `vendor/bin/testo`:

```php
use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\SuiteConfig;

return new ApplicationConfig(
    src: ['app/src'],
    suites: [
        new SuiteConfig(name: 'Feature', location: ['tests/Feature']),
    ],
);
```

Mark the tests with `#[\Testo\Test]` (on the class or on each method) and assert through `Testo\Assert`:

```php
use Testo\Assert;
use Testo\Test;

#[Test]
final class UserControllerTest extends TestCase
{
    public function showsTheProfile(): void
    {
        $this->fakeHttp()->get('/profile')->assertOk();

        Assert::true($this->getContainer()->has(UserRepository::class));
    }
}
```

Every test gets a fresh `TestCase` instance with the application booted in `setUp()`, the same as under PHPUnit.
`setUp()` and `tearDown()` are `#[BeforeTest]` / `#[AfterTest]` hooks that run ahead of, and after, the hooks of
your test class; override them and call the parent, with or without repeating the attribute. A `SkipTest` thrown in
`setUp()` skips the test, any other exception is reported as a test error. Mockery expectations are verified after
each test without any extra configuration.

## Spiral package testing

There are some difference between App and package testing. One of them - tou don't have application and bootloaders.

TestCase from the package has custom TestApp implementation that will help you to test your packages without creating
extra classes.

The following example will show you how it is easy-peasy.

#### Tests folder structure:

```
tests
  - app
    - config
      - my-config.php
    - ...
  - src
    - TestCase.php
    - MyFirstTestCase.php
```

### TestCase configuration

```php
namespace MyPackage\Tests;

abstract class TestCase extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return __DIR__.'/../';
    }

    public function defineBootloaders(): array
    {
        return [
            \MyPackage\Bootloaders\PackageBootloader::class,
            // ...
        ];
    }
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
