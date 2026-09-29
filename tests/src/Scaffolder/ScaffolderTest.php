<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Scaffolder;

use Spiral\Files\FilesInterface;
use Spiral\Testing\Tests\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

final class ScaffolderTest extends TestCase
{
    #[Test]
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

    #[Test]
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

    #[Test]
    public function testCommandNameIsRequired(): void
    {
        Expect::exception(RuntimeException::class)->withMessageContaining('Not enough arguments (missing: "name").');

        $this->assertScaffolderCommandSame(
            'create:command',
            ['-n' => false],
            '',
        );
    }

    #[Test]
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
                'private bool $foo;',
            ],
        );
    }

    #[Test]
    public function testAfterTestFilesShoulBeRestored(): void
    {
        $files = $this->mockContainer(FilesInterface::class);

        $this->assertScaffolderCommandContains(
            'create:command',
            [
                'name' => 'TestCommand',
            ],
            expectedStrings: ['final class TestCommand extends Command'],
        );

        Assert::same($this->getContainer()->get(FilesInterface::class), $files);
    }
}
