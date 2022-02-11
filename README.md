# Spiral Framework testing SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/testing.svg?style=flat-square)](https://packagist.org/packages/spiral/testing)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral/testing.svg?style=flat-square)](https://packagist.org/packages/spiral/testing)

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 7.4+
- Spiral framework 2.9+

## Installation

You can install the package via composer:

```bash
composer require spiral/testing
```

## Spiral App testing

### TestApp configuration

#### Tests folders structure:

```
- tests
    - TestCase.php
    - Unit
      - MyFirstTestCase.php
      - ...
    - Feature
      - Controllers
        - HomeControllerTestCase.php
      ...
    - TestApp.php
```

Create test App class and implement `Spiral\Testing\TestableKernelInterface`

```php
namespace Tests\App;

class TestApp extends \App\App implements \Spiral\Testing\TestableKernelInterface
{
    use \Spiral\Testing\Traits\TestableKernel;
}
```

### TestCase configuration

Extend your TestCase class from `Spiral\Testing\TestCase` and implements a couple of required methods:

```php
namespace Tests;

abstract class TestCase extends \Spiral\Testing\TestCase
{
    public function createAppInstance(): TestableKernelInterface
    {
        return \Spiral\Tests\App\TestApp::create(
            $this->defineDirectories($this->rootDirectory()),
            false
        );
    }
}
```

## Spiral package testing

There are some difference between App and package testing. One of them - tou don't have application and bootloaders.

TestCase from the package has custom TestApp implementation that will help you testing your packages without creating
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

## Usage

### Starting callbacks

If you need to rebind some bound containers, you can do it via starting callbacks. You can create as more callbacks as
you want.

**Make sure that you create callbacks before application run**.

```php
abstract class TestCase extends \Spiral\Testing\TestCase
{
    protected function setUp(): void
    {
        // !!! Before parent::setUp() !!! 
        // Will be fired before method `boot` invoked
        $this->beforeBooting(static function(\Spiral\Core\Container $container) {

            $container->bind(\Spiral\Queue\QueueInterface::class, // ...);
            
        });
        
        // Will be fired after method `boot` invoked, but before method `start`
        $this->beforeStarting(static function(\Spiral\Core\Container $container) {

            $container->bind(\Spiral\Queue\QueueInterface::class, // ...);
            
        });

        parent::setUp();
    }
}
```

### Interaction with Http

```php
$response = $this->fakeHttp()
    ->withHeaders(['Accept' => 'application/json'])
    ->withHeader('CONTENT_TYPE', 'application/json')
    ->withActor(new UserActor())
    ->withServerVariables(['SERVER_ADDR' => '127.0.0.1'])
    ->withAuthorizationToken('token-hash', 'Bearer') // Header => Authorization: Bearer token-hash
    ->withCookie('csrf', '...')
    ->withSession([
        'cart' => [
            'items' => [...]
        ]
    ])
    ->withEnvironment([
        'QUEUE_CONNECTION' => 'sync'
    ])
    ->withoutMiddleware(MyMiddleware::class)
    ->get('/post/1')

$response->assertStatus(200);
```

#### Requests

```php
$http = $this->fakeHttp();
$http->withHeaders(['Accept' => 'application/json']);

$http->get('/')->assertOk();
$http->getJson('/')->assertOk();

$http->post('/')->assertOk();
$http->postJson('/')->assertOk();

$http->put('/')->assertOk();
$http->putJson('/')->assertOk();

$http->delete('/')->assertOk();
$http->deleteJson('/')->assertOk();
```

### Interaction with Mailer

```php
protected function setUp(): void
{
    parent::setUp();
    $this->mailer = $this->fakeMailer();
}

protected function testRegisterUser(): void
{
    // run some code
    
    $this->mailer->assertSent(UserRegisteredMail::class, function (MessageInterface $message) {
        return $message->getTo() === 'user@site.com';
    })
}
```

#### assertSent

```php
$this->mailer->assertSent(UserRegisteredMail::class, function (MessageInterface $message) {
    return $message->getTo() === 'user@site.com';
})
```

#### assertNotSent

```php
$this->mailer->assertNotSent(UserRegisteredMail::class, function (MessageInterface $message) {
    return $message->getTo() === 'user@site.com';
})
```

#### assertSentTimes

```php
$this->mailer->assertSentTimes(UserRegisteredMail::class, 1);
```

#### assertNothingSent

```php
$this->mailer->assertNothingSent();
```

### Interaction with Queue

```php
protected function setUp(): void
{
    parent::setUp();
    $this->connection = $this->fakeQueue();
    $this->queue = $this->connection->getConnection();
}

protected function testRegisterUser(): void
{
    // run some code
    
    $this->queue->assertPushed('mail.job', function (array $data) {
        return $data['handler'] instanceof \Spiral\SendIt\MailJob
            && $data['options']->getQueue() === 'mail'
            && $data['payload']['foo'] === 'bar';
    });
    
    $this->connection->getConnection('redis')->assertPushed('another.job', ...);
}
```

#### assertPushed

```php
$this->mailer->assertPushed('mail.job', function (array $data) {
    return $data['handler'] instanceof \Spiral\SendIt\MailJob
        && $data['options']->getQueue() === 'mail'
        && $data['payload']['foo'] === 'bar';
});
```

#### assertPushedOnQueue

```php
$this->mailer->assertPushedOnQueue('mail', 'mail.job', function (array $data) {
    return $data['handler'] instanceof \Spiral\SendIt\MailJob
        && $data['payload']['foo'] === 'bar';
});
```

#### assertPushedTimes

```php
$this->mailer->assertPushedTimes('mail.job', 2);
```

#### assertNotPushed

```php
$this->mailer->assertNotPushed('mail.job', function (array $data) {
    return $data['handler'] instanceof \Spiral\SendIt\MailJob
        && $data['options']->getQueue() === 'mail'
        && $data['payload']['foo'] === 'bar';
});
```

#### assertNothingPushed

```php
$this->mailer->assertNothingPushed();
```

### Interactions with container

#### assertBootloaderLoaded

```php
$this->assertBootloaderLoaded(\MyPackage\Bootloaders\PackageBootloader::class);
```

#### assertBootloaderMissed

```php
$this->assertBootloaderMissed(\Spiral\Framework\Bootloaders\Http\HttpBootloader::class);
```

#### assertContainerMissed

```php
$this->assertContainerMissed(\Spiral\Queue\QueueConnectionProviderInterface::class);
```

#### assertContainerInstantiable

Checking if container can create an object with autowiring

```php
$this->assertContainerInstantiable(\Spiral\Queue\QueueConnectionProviderInterface::class);
```

#### assertContainerBound

Checking if container has alias and bound with the same interface

```php
$this->assertContainerBound(\Spiral\Queue\QueueConnectionProviderInterface::class);
```

Checking if container has alias with specific class

```php
$this->assertContainerBound(
    \Spiral\Queue\QueueConnectionProviderInterface::class,
    \Spiral\Queue\QueueManager::class
);

// With additional parameters

$this->assertContainerBound(
    \Spiral\Queue\QueueConnectionProviderInterface::class,
    \Spiral\Queue\QueueManager::class,
    [
        'foo' => 'bar'
    ]
);

// With callback

$this->assertContainerBound(
    \Spiral\Queue\QueueConnectionProviderInterface::class,
    \Spiral\Queue\QueueManager::class,
    [
        'foo' => 'bar'
    ],
    function(\Spiral\Queue\QueueManager $manager) {
        $this->assertEquals(..., $manager->....)
    }
);
```

#### assertContainerBoundNotAsSingleton

```php
$this->assertContainerBoundNotAsSingleton(
    \Spiral\Queue\QueueConnectionProviderInterface::class,
    \Spiral\Queue\QueueManager::class
);
```

#### assertContainerBoundAsSingleton

```php
$this->assertContainerBoundAsSingleton(
    \Spiral\Queue\QueueConnectionProviderInterface::class,
    \Spiral\Queue\QueueManager::class
);
```

#### mockContainer

The method will bind alias with mock in the application container.

```php
function testQueue(): void
{
    $manager = $this->mockContainer(\Spiral\Queue\QueueConnectionProviderInterface::class);
    $manager->shouldReceive('getConnection')->once()->with('foo')->andReturn(
        \Mockery::mock(\Spiral\Queue\QueueInterface::class)
    );

    $queue = $this->getContainer()->get(\Spiral\Queue\QueueInterface::class);
}
```

### Interaction with dispatcher

#### assertDispatcherRegistered

```php
$this->assertDispatcherRegistered(HttpDispatcher::class);
```

#### assertDispatcherMissed

```php
$this->assertDispatcherMissed(HttpDispatcher::class);
```

#### serveDispatcher

Check if dispatcher registered in the application and run method serve inside scope with passed bindings.

```php
$this->serveDispatcher(HttpDispatcher::class, [
    \Spiral\Boot\EnvironmentInterface::class => new \Spiral\Boot\Environment([
        'foo' => 'bar'
    ]),
    
]);
```

#### getRegisteredDispatchers

```php
/** @var class-string[] $dispatchers */
$dispatchers = $this->getRegisteredDispatchers();
```

### Interaction with Console

#### assertConsoleCommandOutputContainsStrings

```php
$this->assertConsoleCommandOutputContainsStrings(
    'ping', 
    ['site' => 'https://google.com'], 
    ['Site found', 'Starting ping ...', 'Success!']
);
```

#### runCommand

```php
$output = $this->runCommand('ping', ['site' => 'https://google.com']);

foreach (['Site found', 'Starting ping ...', 'Success!'] as $string) {
    $this->assertStringContaisString($string, $output);
}
```

### Interaction with Config

#### assertConfigMatches

```php
$this->assertConfigMatches('http', [
    'basePath'   => '/',
    'headers'    => [
        'Content-Type' => 'text/html; charset=UTF-8',
    ],
    'middleware' => [],
])
```

#### getConfig

```php
/** @var array $config */
$config = $this->getConfig('http');
```

### Interactions with file system

#### assertDirectoryAliasDefined

```php
$this->assertDirectoryAliasDefined('runtime');
```

#### assertDirectoryAliasMatches

```php
$this->assertDirectoryAliasMatches('runtime', __DIR__.'src/runtime');
```

#### cleanupDirectories

```php
$this->cleanupDirectories(
    __DIR__.'src/runtime/cache', 
    __DIR__.'src/runtime/tmp'
);
```

#### cleanupDirectoriesByAliases

```php
$this->cleanupDirectoriesByAliases(
    'runtime', 'app', '...'
);
```

#### cleanUpRuntimeDirectory

```php
$this->cleanUpRuntimeDirectory();
```

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [butschster](https://github.com/spiral-packages)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
