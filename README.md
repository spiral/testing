# Spiral Framework testing SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/testing.svg?style=flat-square)](https://packagist.org/packages/spiral/testing)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral/testing.svg?style=flat-square)](https://packagist.org/packages/spiral/testing)

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.1+
- Spiral framework 3.0+

Documentation on how to install and use the package can be found on the official documentation
page - [Testing — Getting Started](https://spiral.dev/docs/testing-start)

## Spiral package testing

There are some difference between App and package testing. One of them - tou don't have application and bootloaders.

TestCase from the package has custom TestApp implementation that will help you to test your packages without creating
extra classes.

The following example will show you how it is easy-peasy.

#### Tests folders structure:

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
