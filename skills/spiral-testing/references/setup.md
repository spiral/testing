# Setting up spiral/testing

## Install and register

```bash
composer require --dev spiral/testing
```

It brings Testo and the Mockery bridge along. Declare the suites in `testo.php` (the `testo-configure`
skill covers the file) and register `SpiralTestingPlugin`, application-wide or on the suites that boot
the app:

```php
use Spiral\Testing\SpiralTestingPlugin;

return new ApplicationConfig(
    src: ['app/src'],
    suites: [new SuiteConfig(name: 'Feature', location: ['tests/Feature'])],
    plugins: [new SpiralTestingPlugin()],
);
```

The plugin registers the services the test cases of a suite share. Mockery needs no plugin of its own:
every TestCase already verifies its expectations after each test.

## The base TestCase

Every project gets one abstract `TestCase` extending `Spiral\Testing\TestCase`; the tests extend it.
It answers one question — which application boots — and that answer differs for a package and an app.

### A package

A package has no kernel of its own. The default `createAppInstance()` builds `Spiral\Testing\TestApp`,
a minimal kernel that loads exactly the bootloaders you list:

```php
namespace Vendor\Package\Tests;

abstract class TestCase extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return __DIR__ . '/..';                // holds app/config, runtime, ...
    }

    public function defineBootloaders(): array
    {
        return [
            \Spiral\Boot\Bootloader\ConfigurationBootloader::class,
            \Vendor\Package\Bootloader\PackageBootloader::class,
        ];
    }
}
```

```
tests/
  app/config/        # configs the package reads, e.g. package.php
  runtime/           # scratch space; fakeStorage() writes under runtime/testing/disks
  src/
    TestCase.php
    PackageBootloaderTest.php
```

`defineDirectories()` maps `root`, `app`, `runtime` and `cache` from `rootDirectory()`; override it to
add aliases or move one.

### An application

An application boots its own kernel. Make the kernel testable and return it from `createAppInstance()`;
`defineBootloaders()` stays unused, since the kernel lists its bootloaders itself:

```php
namespace Tests;

use Spiral\Core\Container;
use Spiral\Testing\TestableKernelInterface;
use Spiral\Testing\Traits\TestableKernel;

final class TestKernel extends \App\Application\Kernel implements TestableKernelInterface
{
    use TestableKernel;
}

abstract class TestCase extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return __DIR__ . '/..';
    }

    public function createAppInstance(Container $container = new Container()): TestableKernelInterface
    {
        return TestKernel::create(
            directories: $this->defineDirectories($this->rootDirectory()),
            handleErrors: false,
            container: $container,
        );
    }
}
```

`handleErrors: false` lets an exception reach the test instead of the kernel's error handler.

### Env for every test

`public const ENV = ['APP_ENV' => 'testing', ...];` on the base TestCase sets env for all its tests; a
subclass can override the constant, and `#[Env]` overrides single keys (see `app-under-test.md`).

## Done

Setup is done when a smoke test on the base TestCase passes under
`vendor/bin/testo --json --path=<that test>`. It proves the kernel boots and loads what you expect:

```php
#[Test]
final class BootTest extends TestCase
{
    public function bootsThePackageBootloader(): void
    {
        $this->assertBootloaderRegistered(\Vendor\Package\Bootloader\PackageBootloader::class);
    }
}
```
