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

Every test gets a fresh `TestCase` instance and a freshly booted application, the same as under PHPUnit. Put the
per-test preparation into `#[BeforeTest]` / `#[AfterTest]` methods: they run with the application already booted.
Overriding `setUp()` / `tearDown()` still works but is deprecated. A `SkipTest` thrown from a hook skips the test,
any other exception is reported as a test error. Mockery expectations are verified after each test without any extra
configuration.

### Shaping the application with attributes

The application is set up before the lifecycle hooks run, so whatever it needs at boot time is declared up front,
on the test method or on the class:

```php
use Spiral\Testing\Attribute\BeforeBooting;
use Spiral\Testing\Attribute\BeforeInit;
use Spiral\Testing\Attribute\Config;
use Spiral\Testing\Attribute\Env;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\Attribute\WithoutExceptionHandling;

#[Env('APP_ENV', 'testing')]
#[BeforeBooting('registerFakes')]           // a method of the test class, of any visibility
final class OrderTest extends TestCase
{
    #[Env('QUEUE', 'sync')]                 // overrides the class for this test
    #[Config('app.debug', true)]
    #[BeforeInit([Seeder::class, 'prepare'])]
    #[TestScope('http', bindings: [Clock::class => FrozenClock::class])]
    #[WithoutExceptionHandling]
    #[Test]
    public function placesAnOrder(): void { /* ... */ }

    private function registerFakes(Container $container): void { /* ... */ }
}
```

`#[TestScope]` wraps the lifecycle hooks too, so a `#[BeforeTest]` method already works with the scoped services.
`beforeBooting()` and `beforeInit()` are deprecated in favour of `#[BeforeBooting]` and `#[BeforeInit]`; called once
the application has booted they throw. A test case with `MAKE_APP_ON_STARTUP = false` boots the application itself
with `initApp()`, and can't use `#[TestScope]`.

### Extending the test pipeline

The attributes above are plain Testo interceptors ordered by the stages of `Spiral\Testing\Stage`:
`INSTANCE` → `CONFIGURE` → `BOOT` → `SCOPE` → `SCOPED`. An interceptor of your own takes one of these orders and
reaches the application under test through `Spiral\Testing\AppContext`:

```php
#[InterceptorOptions(order: Stage::SCOPED)]
final readonly class TransactionInterceptor implements TestRunInterceptor
{
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $db = AppContext::fromTest($info, WithTransaction::class)->getContainer()->get(DatabaseInterface::class);

        $db->begin();
        try {
            return $next($info);
        } finally {
            $db->rollback();
        }
    }
}
```

Between `CONFIGURE` and `BOOT` an interceptor still adds env and boot callbacks to the `AppContext`; from `SCOPED` on
the container it hands out is the scoped one.

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
