<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Scaffolder;

use Spiral\Files\FilesInterface;
use Spiral\Testing\Tests\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;

final class ScaffolderTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $this->assertScaffolderCommandSame(
            'create:command',
            [
                'name' => 'TestCommand',
            ],
            expected: <<<'PHP'
<?php

declare(strict_types=1);

namespace Spiral\Testing\Command;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Console\Command;

#[AsCommand(name: 'test:command')]
final class TestCommand extends Command
{
    public function __invoke(): int
    {
        // Put your command logic here
        $this->info('Command logic is not implemented yet');

        return self::SUCCESS;
    }
}

PHP,
            expectedFilename: 'app/src/Command/TestCommand.php',
            expectedOutputStrings: [
                "Declaration of 'TestCommand' has been successfully written into 'app/src/Command/TestCommand.php",
            ],
        );
    }

    public function testCreateCommandContainsNamespace(): void
    {
        $this->assertScaffolderCommandContains(
            'create:command',
            [
                'name' => 'TestCommand',
                '--namespace' => 'App\Command',
            ],
            expectedStrings: [
                'namespace App\Command;',
            ],
            expectedFilename: 'app/src/TestCommand.php',
        );
    }

    public function testCommandNameIsRequired(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "name").');

        $this->assertScaffolderCommandSame(
            'create:command',
            ['-n' => false],
            '',
        );
    }

    public function testCreateCommandWithAdditionalOptions(): void
    {
        $this->assertScaffolderCommandContains(
            'create:command',
            [
                'name' => 'TestCommand',
                '-o' => 'foo',
            ],
            expectedStrings: [
                "#[Option(description: 'Argument description')]",
                'private bool $foo;'
            ],
        );
    }

    public function testAfterTestFilesShoulBeRestored(): void
    {
        $files = $this->mockContainer(FilesInterface::class);

        $this->assertScaffolderCommandContains(
            'create:command',
            [
                'name' => 'TestCommand'
            ],
            expectedStrings: ['final class TestCommand extends Command'],
        );

        $this->assertSame($files, $this->getContainer()->get(FilesInterface::class));
    }
}
