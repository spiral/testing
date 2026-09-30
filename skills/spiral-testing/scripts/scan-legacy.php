<?php

/**
 * Lists the spiral/testing 3.x (PHPUnit) constructs left in a test suite, each with its 4.x replacement.
 *
 * Usage (from the project root):
 *   php <skillDir>/scripts/scan-legacy.php [<dir-or-file> ...]    # default: tests
 *
 * `$this->assertX()` calls are told apart from the spiral/testing helpers by reading the installed
 * TestCase; without vendor/autoload.php that check is skipped and said so.
 *
 * Exit codes: 0 nothing left, 1 findings listed, 2 a path does not exist.
 */

declare(strict_types=1);

$paths = array_slice($argv, 1) ?: ['tests'];

/** @var array<string, array{string, string}> Pattern => [construct, replacement]. */
$rules = [
    '/function\s+setUp\s*\(\s*\)/' => [
        'setUp() override',
        'a #[BeforeTest] method of your own, without parent::setUp(); boot-time code before the parent call becomes #[Env] / #[Config] / #[BeforeInit] / #[BeforeBooting]',
    ],
    '/function\s+tearDown\s*\(\s*\)/' => [
        'tearDown() override',
        'an #[AfterTest] method of your own, without parent::tearDown()',
    ],
    '/function\s+(setUp|tearDown)[A-Z]\w*\s*\(/' => [
        'setUp<Trait>() / tearDown<Trait>() convention',
        '#[BeforeTest] / #[AfterTest] on the method; the convention is no longer called',
    ],
    '/->beforeBooting\s*\(/' => [
        'beforeBooting() call',
        "#[BeforeBooting('method')] on the test or the class",
    ],
    '/->beforeInit\s*\(/' => [
        'beforeInit() call',
        "#[BeforeInit('method')] on the test or the class",
    ],
    '/MAKE_APP_ON_STARTUP/' => [
        'MAKE_APP_ON_STARTUP',
        'removed: the app always boots; declare boot input with attributes, or initApp($env) to boot again',
    ],
    '/getTestAttributes\s*\(/' => [
        'getTestAttributes()',
        'removed: an attribute with its own interceptor (references/extending.md)',
    ],
    '/function\s+invokeTestMethod\s*\(/' => [
        'invokeTestMethod() override',
        'removed: an interceptor at Stage::SCOPED (references/extending.md)',
    ],
    '/MockeryPHPUnitIntegration/' => [
        'MockeryPHPUnitIntegration',
        'drop it: Mockery expectations are verified after each test',
    ],
    '/PHPUnit\\\\/' => [
        'PHPUnit class reference',
        'the Testo counterpart (testo-migrate-from-phpunit skill)',
    ],
];

$helpers = null;
for ($dir = getcwd(); $dir !== dirname($dir); $dir = dirname($dir)) {
    if (is_file($dir . '/vendor/autoload.php')) {
        require $dir . '/vendor/autoload.php';
        break;
    }
}
if (class_exists(Spiral\Testing\TestCase::class)) {
    $helpers = array_map(
        static fn(ReflectionMethod $m): string => strtolower($m->getName()),
        (new ReflectionClass(Spiral\Testing\TestCase::class))->getMethods(),
    );
}

$files = [];
foreach ($paths as $path) {
    if (is_file($path)) {
        $files[] = $path;
        continue;
    }
    if (!is_dir($path)) {
        fwrite(STDERR, "no such file or directory: {$path}\n");
        exit(2);
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}
sort($files);

$findings = 0;
$byConstruct = [];
foreach ($files as $file) {
    $rows = [];
    foreach (file($file) ?: [] as $index => $line) {
        foreach ($rules as $pattern => [$construct, $replacement]) {
            if (preg_match($pattern, $line)) {
                $rows[] = [$index + 1, $construct, $replacement];
            }
        }

        if ($helpers !== null && preg_match_all('/(?:\$this->|self::|static::)(assert\w+)\s*\(/', $line, $m)) {
            foreach ($m[1] as $method) {
                if (!in_array(strtolower($method), $helpers, true)) {
                    $rows[] = [$index + 1, "PHPUnit {$method}()", 'Testo\Assert, with the actual value first (testo-migrate-from-phpunit skill)'];
                }
            }
        }
    }

    if ($rows === []) {
        continue;
    }

    echo "\n{$file}\n";
    foreach ($rows as [$lineNo, $construct, $replacement]) {
        echo "  {$lineNo}: {$construct} → {$replacement}\n";
        $byConstruct[$construct] = ($byConstruct[$construct] ?? 0) + 1;
        ++$findings;
    }
}

echo "\n";
if ($helpers === null) {
    echo "Note: spiral/testing is not installed here, so PHPUnit \$this->assert*() calls were not checked.\n";
}
if ($findings === 0) {
    echo "No spiral/testing 3.x constructs found in " . count($files) . " file(s).\n";
    exit(0);
}

arsort($byConstruct);
echo "{$findings} finding(s) in " . count($files) . " scanned file(s):\n";
foreach ($byConstruct as $construct => $count) {
    echo "  {$count} × {$construct}\n";
}
exit(1);
