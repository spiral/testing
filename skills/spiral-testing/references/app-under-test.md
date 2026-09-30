# Shaping the app under test

Everything the app needs **at boot** is declared with attributes from `Spiral\Testing\Attribute`, on
the test method or on the class. A method attribute applies to that test; a class attribute applies to
every test of the class and comes first, so the method one wins where both set the same thing.

| Attribute | Stage | Effect |
|---|---|---|
| `#[Env(key, value)]` | configure | Sets an env variable. Repeatable. |
| `#[Config('section.key', value)]` | configure | Patches a config value while the app boots. Repeatable. |
| `#[BeforeInit(callback)]` | configure | Runs the callback on the container before the app boots. Repeatable. |
| `#[BeforeBooting(callback)]` | configure | Registers the callback as a booting callback of the kernel. Repeatable. |
| `#[WithoutExceptionHandling]` | configure | Exceptions escape to the test instead of being rendered or reported. |
| `#[TestScope(scopes, bindings)]` | scope | Runs the hooks and the test inside container scopes. |

## Env

The env of a test is layered: the `ENV` constant of the test case, then class `#[Env]`, then method
`#[Env]`, each overriding the keys it names.

```php
#[Env('QUEUE_CONNECTION', 'sync')]
#[Test]
public function dispatchesSynchronously(): void { /* ... */ }
```

`$this->withEnvironment([...])` rebinds `EnvironmentInterface` at run time without booting again: code
that reads env on each call sees the change, anything that read it during boot does not.

## Config

`#[Config('app.debug', true)]` — the first segment names the config section, the rest is the key
inside it. `closure:` takes a callable computing the value instead of `value:`.

At run time `getConfig()` reads a section and `assertConfigMatches()` / `assertConfigHasFragments()`
assert on it. `updateConfig()` and `setConfig()` exist too, but Spiral freezes a section once something
has read it and then throws `ConfigDeliveredException` — declare the value with `#[Config]` instead.

## Boot callbacks

`#[BeforeInit]` runs on the container before the kernel starts: bind what the bootloaders will
resolve. `#[BeforeBooting]` joins the kernel's booting callbacks. Both autowire the callback's
arguments. The callback is:

- the name of a method of the test class, of any visibility — `#[BeforeBooting('fakeClock')]`;
- a callable string or array — `#[BeforeInit([Seeder::class, 'bind'])]`;
- a closure, where PHP allows one in an attribute (PHP 8.5+).

```php
#[BeforeInit('bindClock')]
#[Test]
public function stampsTheOrder(): void { /* ... */ }

private function bindClock(Container $container): void
{
    $container->bindSingleton(ClockInterface::class, new FrozenClock('2026-01-01'));
}
```

## Scopes

`#[TestScope('http')]`, or a list `#[TestScope(['http', 'request'])]` from the outermost in. The
bindings go to the innermost scope: `#[TestScope('http', bindings: [Clock::class => FrozenClock::class])]`.
A scope the container is already in is skipped. A method attribute replaces the class one entirely.

The `#[BeforeTest]` / `#[AfterTest]` hooks run inside the scopes too, so a hook reaches scoped services
the same way the test does; `$this->getContainer()` returns the innermost scope.

For one block of a test, `$this->runScoped(fn(Container $c) => ..., bindings: [...])` enters a scope
right there.

## Exception handling

`#[WithoutExceptionHandling]` — or `$this->withoutExceptionHandling()` inside the test — swaps the
app's exception handler for one that rethrows. Reach for it when an HTTP test answers 500 and you need
the exception behind it.

## Lifecycle hooks

`#[BeforeTest]` / `#[AfterTest]` methods (from `Testo\Lifecycle`) run after the app has booted and
before it shuts down. A `SkipTest` thrown from one skips the test; any other exception reports the test
as an error. The fakes, requests and per-test fields belong here or in the test body.

## Booting again

`$this->initApp($env)` boots a fresh app within the running test, with `$env` on top of the declared
env — for a test that compares two configurations, or one that has to observe the boot itself. The
boot callbacks declared for the test apply again.

## Deprecated

`beforeBooting()` and `beforeInit()` still exist on the TestCase, but called from a hook or a test they
come too late and throw a `LogicException`. Use `#[BeforeBooting]` and `#[BeforeInit]`.
