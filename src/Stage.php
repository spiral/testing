<?php

declare(strict_types=1);

namespace Spiral\Testing;

/**
 * Interceptor orders reserved for the application under test, for use in
 * {@see \Testo\Pipeline\Attribute\InterceptorOptions::$order}.
 *
 * The range sits inside the Testo assertion collector, so assertions made by these interceptors count,
 * and outside Mockery and the lifecycle hooks. Data sets, retries and repeats run outside it, so each
 * of them gets a fresh test instance and a fresh application.
 *
 * An interceptor placed between two stages sees the {@see AppContext} in the state the earlier stage
 * left it: `$info->getAttribute(AppContext::class)`.
 */
final class Stage
{
    /** A fresh test instance and its {@see AppContext} are created. */
    public const INSTANCE = 100_000;

    /** Env, configs and boot callbacks are collected into the {@see AppContext}. */
    public const CONFIGURE = 200_000;

    /** The application boots. */
    public const BOOT = 300_000;

    /** The container scopes declared by {@see Attribute\TestScope} are entered. */
    public const SCOPE = 400_000;

    /** Inside the test scopes: scoped services are available from {@see AppContext::getContainer()}. */
    public const SCOPED = 500_000;

    private function __construct() {}
}
