<?php

declare(strict_types=1);

namespace Spiral\Testing;

use Internal\Container\Container;
use Spiral\Testing\Internal\Deprecations;
use Testo\Common\Messenger;
use Testo\Common\PluginConfigurator;

/**
 * Registers the services the {@see TestCase} interceptors share within a suite.
 *
 * @api
 */
final readonly class SpiralTestingPlugin implements PluginConfigurator
{
    #[\Override]
    public function configure(Container $container): void
    {
        $container->set(new Deprecations($container->get(Messenger::class)));
    }
}
