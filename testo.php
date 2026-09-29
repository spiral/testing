<?php

declare(strict_types=1);

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;

\getenv('DEBUG') === false and \putenv('DEBUG=true') and $_ENV['DEBUG'] = 'true';

return new ApplicationConfig(
    src: new FinderConfig(include: ['src']),
    suites: [
        new SuiteConfig(
            name: 'Tests',
            location: new FinderConfig(include: ['tests/src']),
        ),
    ],
);
