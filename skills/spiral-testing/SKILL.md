---
name: spiral-testing
description: Test a Spiral Framework application or package with spiral/testing on Testo — the `Spiral\Testing\TestCase` base, fakes (HTTP, queue, mailer, events, storage), app-shaping attributes (`#[Env]`, `#[Config]`, `#[TestScope]`, `#[BeforeBooting]`) and interceptors on `Stage` / `AppContext`. Use when writing or setting up tests that boot a Spiral app, faking a Spiral service in a test, or upgrading tests from spiral/testing 3.x.
---

# Testing Spiral apps with spiral/testing

spiral/testing boots a Spiral application for every test and hands the test helpers to drive it and
assert on it. It runs on Testo: for Testo itself — `#[Test]`, `Assert`, `Expect`, data providers,
running the suite — use the `testo-*` skills; this one covers what spiral/testing adds on top. When a
name here disagrees with the installed `vendor/spiral/testing`, the installed source wins.

## How a test runs

Each test goes through Testo interceptors ordered by the `Spiral\Testing\Stage` constants:

1. `INSTANCE` — a fresh instance of the test class and its `AppContext`. Nothing carries over between
   tests, data sets or retries.
2. `CONFIGURE` — `#[Env]`, `#[Config]`, `#[BeforeInit]`, `#[BeforeBooting]` feed the context.
3. `BOOT` — the app boots from the test case's `createAppInstance()`, `defineBootloaders()`,
   `defineDirectories()` and `ENV`.
4. `SCOPE` — `#[TestScope]` enters its container scopes.
5. `#[BeforeTest]` hooks → test body → `#[AfterTest]` hooks, all inside those scopes; then Mockery
   expectations are verified.

The line that decides where code goes: **boot-time** input — env, configs, bindings the bootloaders
read — is declared up front with attributes, because by the time a hook runs the app has booted.
**Run-time** work — fakes, requests, assertions — goes into hooks and test bodies.

## Pick the reference

| Task | Read |
|---|---|
| Adding spiral/testing to a project; the base TestCase of an app or a package | `references/setup.md` |
| Env, configs, boot callbacks, scopes or exception handling for a test or a class; lifecycle hooks; booting again mid-test | `references/app-under-test.md` |
| HTTP requests, console commands, faking queue / mail / events / storage, asserting on the container, config, views | `references/helpers.md`, exact signatures from `scripts/list-helpers.php` |
| An attribute or interceptor of your own — a DB transaction per test, seeding, a scoped service | `references/extending.md` |
| Tests written for spiral/testing 3.x (PHPUnit), or deprecation lines on stderr | `references/upgrade-from-3.md` with `scripts/scan-legacy.php` |

Scripts run from the project root: `php <skillDir>/scripts/<script>.php`, where `<skillDir>` is the
folder holding this file.

## Rules for every test

- Mark tests with `#[\Testo\Test]`. On the class it is safe: the TestCase helpers stay out of discovery.
- Assert with `Testo\Assert` (actual value first) or the `assert*` helpers of the TestCase and the
  fakes. The PHPUnit `$this->assert*()` methods do not exist.
- Put per-test preparation into a `#[BeforeTest]` method of your own. A `setUp()` override still runs
  but is deprecated.
- Fake a service before the code under test resolves it: `fakeQueue()`, `fakeMailer()` and friends
  rebind the container, and an object already holding the real service keeps it.
- Keep per-test state in properties: every test has its own instance, so a hook sets up exactly the
  test that follows it.

## Done

A test task is done when:

- `vendor/bin/testo --json --path=<test file>` reports `"status": "passed"`, and every test you added
  is listed in the report — a method without `#[Test]` drops out silently;
- the run prints no deprecation line naming your classes on stderr;
- each behaviour the task names has an assertion that fails when that behaviour breaks.

The references add their own criteria where a task has more to finish than a green run.
