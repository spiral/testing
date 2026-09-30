# Driving the app and asserting on it

Pick the helper by what the test exercises. The recipes show the shape; exact signatures come from the
installed source:

```bash
php <skillDir>/scripts/list-helpers.php --filter=<word>   # e.g. queue, cookie, bootloader
```

Every `fake*()` helper rebinds a service in the container and returns the fake. Call it before the
code under test resolves that service — in a `#[BeforeTest]` hook or at the top of the test.

## HTTP

`$this->fakeHttp()` sends requests through the app's HTTP pipeline and returns a `TestResponse`. Each
request runs in the `http` scope, and the app's finalizers run after it.

```php
$response = $this->fakeHttp()
    ->withActor($user)                         // authenticated as $user, whatever auth transport the app uses
    ->withHeader('Accept-Language', 'de')
    ->postJson('/orders', ['sku' => 'A-1']);

$response->assertCreated()->assertHasHeader('Location');
Assert::same($response->getJsonParsedBody()['sku'], 'A-1');
```

- Request state: `withHeaders()`, `withCookies()`, `withSession()`, `withServerVariables()`,
  `withAuthorizationToken()`; the `flush*()` twins reset them.
- Pipeline: `withMiddleware(...)` prepends middleware, `withoutMiddleware(...)` drops it.
- Uploads: `$this->getFileFactory()->createFile('a.pdf', 120)`, `createImage()`,
  `createFileWithContent()`, passed as the `files` argument.
- Response: `assertStatus()` and named shortcuts (`assertOk()`, `assertNotFound()`, ...),
  `assertBody*()`, `assertCookie*()`, `assertHasHeader()`; they return the response for chaining.
  `getOriginalResponse()` hands over the PSR-7 response for anything else.

A 500 with no clue: add `#[WithoutExceptionHandling]` to see the exception (`app-under-test.md`).

## Console

```php
$output = $this->runCommand('cache:clean', ['--force' => true]);
Assert::string($output)->contains('Cache cleared');

$this->assertConsoleCommandOutputContainsStrings('cache:clean', ['--force' => true], 'Cache cleared');
$this->assertCommandRegistered('cache:clean');
```

## Container and bootloaders

- `assertBootloaderRegistered()` / `assertBootloaderMissed()` — what the kernel loaded.
- `assertContainerBound()`, `assertContainerBoundAsSingleton()`, `assertContainerBoundNotAsSingleton()`,
  `assertContainerInstantiable()`, `assertContainerMissed()` — how a service resolves.
- `mockContainer(Foo::class)` binds a Mockery mock as a singleton and returns it; its expectations are
  verified after the test.
- `getContainer()` for anything else; inside a `#[TestScope]` it is the scoped container.

## Events

```php
$events = $this->fakeEventDispatcher([OrderPlaced::class]);   // [] records every event
$this->fakeHttp()->post('/orders', [...]);

$events->assertDispatched(OrderPlaced::class, fn(OrderPlaced $e) => $e->sku === 'A-1');
```

The fake replaces the dispatcher, so listeners do not run; `decorate: true` keeps the real dispatcher
behind it and the listeners run as well. `assertListening(Event::class, Listener::class)` checks the
wiring without dispatching anything.

## Mail

```php
$mailer = $this->fakeMailer();
// act
$mailer->assertSent(WelcomeMail::class, fn(WelcomeMail $m) => $m->getTo() === ['ann@example.com']);
```

`assertSent()` and `assertSentTimes()` return the matching messages for further assertions.

## Queue

```php
$queue = $this->fakeQueue()->getConnection();          // the default connection; pass a name for another
// act
$queue->assertPushed(SendInvoice::class, fn(array $job) => $job['payload']['id'] === 42);
```

A job is `['name' => ..., 'payload' => [...], 'options' => ..., 'handler' => ...]`. `assertPushedOnQueue()`
checks the queue name from the options.

## Storage

`$this->fakeStorage()` swaps every configured bucket for a fake writing under
`runtime/testing/disks`, wiped on each call. `$storage->bucket('uploads')->assertCreated('a.txt')`,
plus `assertExists()`, `assertDeleted()`, `assertMoved()`, `assertCopied()`, `assertVisibilityChanged()`
and their negations.

## The rest

- Views: `assertViewSame()`, `assertViewContains()`, `assertViewNotContains()`.
- Translator: `withLocale('de')`, `getTranslator()`.
- Scaffolder: `assertScaffolderCommandSame()` / `assertScaffolderCommandContains()` run a generator
  command without writing files and compare what it would write.
- Dispatchers: `assertDispatcherRegistered()`, `assertDispatcherCanBeServed()`, `serveDispatcher()`.
- Directories: `getDirectoryByAlias()`, `assertDirectoryAliasMatches()`, `cleanupDirectories()`.
