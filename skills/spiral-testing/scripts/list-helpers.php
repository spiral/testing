<?php

/**
 * Lists the helpers of the installed spiral/testing: what a TestCase subclass can call on `$this`, and
 * what the fakes and the HTTP response return to assert on. Read from the installed source, so the
 * list always matches the version in vendor/.
 *
 * Usage (from the project root):
 *   php <skillDir>/scripts/list-helpers.php                  # everything
 *   php <skillDir>/scripts/list-helpers.php --filter=queue   # only groups/methods matching the word
 *
 * Exit codes: 0 ok, 1 no autoloader or spiral/testing not installed.
 */

declare(strict_types=1);

$filter = null;
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--filter=')) {
        $filter = strtolower(substr($arg, 9));
    }
}

$autoload = null;
for ($dir = getcwd(); $dir !== dirname($dir); $dir = dirname($dir)) {
    if (is_file($dir . '/vendor/autoload.php')) {
        $autoload = $dir . '/vendor/autoload.php';
        break;
    }
}
if ($autoload === null) {
    fwrite(STDERR, "vendor/autoload.php not found above " . getcwd() . "\n");
    exit(1);
}
require $autoload;

if (!class_exists(Spiral\Testing\TestCase::class)) {
    fwrite(STDERR, "spiral/testing is not installed in this project\n");
    exit(1);
}

/** What each group is for, so the agent can pick one without reading every signature. */
$groups = [
    Spiral\Testing\TestCase::class => 'Methods on `$this` in a TestCase subclass, by trait',
    Spiral\Testing\Http\FakeHttp::class => '`$this->fakeHttp()` — sends requests into the app',
    Spiral\Testing\Http\TestResponse::class => 'Returned by FakeHttp requests — assert on the response',
    Spiral\Testing\Mailer\FakeMailer::class => '`$this->fakeMailer()` — captures sent mail',
    Spiral\Testing\Queue\FakeQueueManager::class => '`$this->fakeQueue()` — hands out fake queues',
    Spiral\Testing\Queue\FakeQueue::class => '`$this->fakeQueue()->getConnection()` — captures pushed jobs',
    Spiral\Testing\Events\FakeEventDispatcher::class => '`$this->fakeEventDispatcher()` — captures dispatched events',
    Spiral\Testing\Storage\FakeBucket::class => '`$this->fakeStorage()->bucket()` — captures file operations',
];

$signature = static function (ReflectionMethod $m): string {
    $params = [];
    foreach ($m->getParameters() as $p) {
        $s = ($p->hasType() ? $p->getType() . ' ' : '') . ($p->isVariadic() ? '...' : '') . '$' . $p->getName();
        if ($p->isDefaultValueAvailable()) {
            $value = $p->getDefaultValue();
            $default = match (true) {
                $p->isDefaultValueConstant() => $p->getDefaultValueConstantName(),
                is_object($value) => 'new ' . (new ReflectionClass($value))->getShortName() . '()',
                $value === [] => '[]',
                default => strtolower(var_export($value, true)) === 'null' ? 'null' : var_export($value, true),
            };
            $s .= ' = ' . preg_replace('/\s+/', ' ', (string) $default);
        }
        $params[] = $s;
    }
    $return = $m->hasReturnType() ? ': ' . $m->getReturnType() : '';
    $mods = $m->isStatic() ? 'static ' : '';

    return $mods . $m->getName() . '(' . implode(', ', $params) . ')' . $return;
};

$summary = static function (ReflectionMethod $m): string {
    $doc = $m->getDocComment();
    if ($doc === false) {
        return '';
    }
    $deprecated = str_contains($doc, '@deprecated') ? ' **deprecated**' : '';
    foreach (preg_split('/\R/', $doc) as $line) {
        $line = trim($line, " \t/*");
        if ($line !== '' && $line[0] === '@') {
            break;
        }
        if ($line !== '') {
            return $deprecated . ' — ' . $line;
        }
    }

    return $deprecated;
};

/**
 * @return array<string, list<ReflectionMethod>> Methods keyed by the trait (or class) declaring them.
 */
$collect = static function (ReflectionClass $class): array {
    $byTrait = [];
    $owners = [];
    foreach ($class->getTraits() as $trait) {
        foreach ($trait->getMethods() as $method) {
            $owners[$method->getName()] = $trait->getShortName();
        }
    }

    foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED) as $method) {
        $declaring = $method->getDeclaringClass()->getName();
        if (!str_starts_with($declaring, 'Spiral\\Testing\\') || str_starts_with($method->getName(), '__')) {
            continue;
        }
        $byTrait[$owners[$method->getName()] ?? $class->getShortName()][] = $method;
    }
    ksort($byTrait);

    return $byTrait;
};

foreach ($groups as $className => $purpose) {
    if (!class_exists($className)) {
        continue;
    }

    $lines = [];
    foreach ($collect(new ReflectionClass($className)) as $owner => $methods) {
        $section = [];
        foreach ($methods as $method) {
            $row = sprintf(
                '- `%s%s`%s',
                $method->isProtected() ? 'protected ' : '',
                $signature($method),
                $summary($method),
            );
            if ($filter === null || str_contains(strtolower($owner . ' ' . $purpose . ' ' . $row), $filter)) {
                $section[] = $row;
            }
        }
        if ($section !== []) {
            $lines[] = "\n### {$owner}\n\n" . implode("\n", $section);
        }
    }

    if ($lines !== []) {
        echo "\n## ", $className, "\n\n", $purpose, "\n", implode("\n", $lines), "\n";
    }
}
