# An attribute or interceptor of your own

When tests need something around them that the built-in attributes do not cover — a transaction rolled
back after each test, seed data, a service bound in the test scope — write a Testo interceptor ordered
by a `Spiral\Testing\Stage` constant and reach the app through `Spiral\Testing\AppContext`. The generic
interceptor mechanics (pipeline levels, results, events) are in the `testo-plugin-author` skill; this
file covers the spiral/testing side.

## Pick the stage

| Order | The `AppContext` there | Fit for |
|---|---|---|
| between `CONFIGURE` and `BOOT` | not booted: `addEnv()`, `beforeInit()`, `beforeBooting()`, `afterBoot()` | anything the app must know while booting |
| `BOOT` + n, before `SCOPE` | booted, root container | work on the root container before scopes open |
| `SCOPED` | booted, inside the test scopes: `getContainer()` is the scoped one | transactions, fixtures, anything tied to the scope |

Everything in the range runs inside the Testo assertion collector: an `Assert` in the interceptor
counts, and a failing one fails the test. Retries, repeats and data sets run outside it, so each gets
its own app and its own pass through your interceptor.

## Write the pair

An attribute that names its interceptor needs no registration:

```php
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
#[FallbackInterceptor(TransactionInterceptor::class)]
final class WithTransaction implements Interceptable {}

#[InterceptorOptions(order: Stage::SCOPED)]
final readonly class TransactionInterceptor implements TestRunInterceptor
{
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $db = AppContext::fromTest($info, WithTransaction::class)
            ->getContainer()
            ->get(DatabaseInterface::class);

        $db->begin();
        try {
            return $next($info);
        } finally {
            $db->rollback();
        }
    }
}
```

- `AppContext::fromTest($info, Attr::class)` returns the context, and throws a `LogicException` naming
  the attribute when the test does not extend the spiral/testing TestCase.
- Testo keeps one interceptor per class for a test, however many attributes point at it. For a
  repeatable attribute, read them all: `$info->getAttribute(Attr::class, [])` lists the class ones
  first, then the method ones.
- `$context->testCase` is the test instance, for an interceptor that has to hand something to the test.
- Constructor arguments of an interceptor after the attribute are autowired from the Testo container;
  services shared by a suite belong in a plugin (`testo-plugin-author`).

## Done

The interceptor is done when a test carrying the attribute passes, and a second test proves the
interceptor acted — it observes the effect (the rolled-back row is gone, the scoped binding resolves)
rather than trusting that the attribute is there.
