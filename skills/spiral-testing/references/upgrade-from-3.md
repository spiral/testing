# Upgrading tests from spiral/testing 3.x

spiral/testing 3.x extended PHPUnit; 4.x runs on Testo and brings the app up from interceptors. The
move has two layers: the PHPUnit → Testo port every suite goes through, and the spiral/testing changes
on top.

## 1. Find what needs porting

```bash
php <skillDir>/scripts/scan-legacy.php tests      # one or more directories or files
```

It lists each 3.x construct by file and line with its replacement, and exits 1 while any remain. Keep
its output as the work-list.

## 2. Port PHPUnit to Testo

Follow the `testo-migrate-from-phpunit` skill for the generic part: `#[Test]`, `Assert` with the
actual value first, `Expect::exception()`, data providers, skipping. Two things specific to a
spiral/testing suite:

- Rector's `PHPUNIT_TO_TESTO` set marks the test methods of classes extending your own base TestCase
  with `#[Test]` and leaves `extends TestCase` in place — that is correct here.
- Rector puts `#[BeforeTest]` on a `setUp()` override. It runs once, but the override is deprecated:
  finish it with the next step.

## 3. Port the spiral/testing constructs

| 3.x | 4.x |
|---|---|
| `setUp()` / `tearDown()` override calling the parent | A `#[BeforeTest]` / `#[AfterTest]` method of your own, without the parent call |
| Code in `setUp()` before `parent::setUp()` — env, configs, bindings for the boot | `#[Env]`, `#[Config]`, `#[BeforeInit]`, `#[BeforeBooting]` (`app-under-test.md`) |
| `$this->beforeBooting(fn)` / `$this->beforeInit(fn)` | `#[BeforeBooting('method')]` / `#[BeforeInit('method')]` |
| `MAKE_APP_ON_STARTUP = false` + `initApp()` in the test | The app always boots; declare boot input with attributes, or `initApp($env)` to boot again |
| `setUp<TraitName>()` / `tearDown<TraitName>()` methods in a trait | `#[BeforeTest]` / `#[AfterTest]` on the trait method |
| `getTestAttributes(Attr::class)` in a custom hook | An attribute with its own interceptor (`extending.md`) |
| Overriding `invokeTestMethod()` | An interceptor at `Stage::SCOPED` (`extending.md`) |
| `MockeryPHPUnitIntegration` | Nothing — Mockery expectations are verified after each test |
| `PHPUnit\Framework\ExpectationFailedException` in `expectException()` | `Expect::exception(Testo\Assert\State\Assertion\AssertionException::class)` |
| `<env>` in `phpunit.xml` | `ENV` on the base TestCase, `#[Env]`, or the top of `testo.php` |

`#[Env]`, `#[Config]`, `#[TestScope]` and `#[WithoutExceptionHandling]` keep their names and meaning,
and now also work on the class. `#[TestScope]` covers the lifecycle hooks as well as the test body.

## Done

The upgrade is done when:

- `scan-legacy.php` over the test directories exits 0;
- `vendor/bin/testo --json` reports `"status": "passed"` with the same number of tests the PHPUnit run
  had — compare the totals, a test that lost its discovery drops out without failing;
- the run prints no deprecation line naming your classes on stderr.
