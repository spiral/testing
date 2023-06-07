<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Mailer\MailerInterface;
use Spiral\Testing\Mailer\FakeMailer;
use Spiral\Testing\Traits\InteractsWithMailer;

/**
 * @coversDefaultClass InteractsWithMailer
 */
final class InteractsWithMailerTest extends TestCase
{
    public function test(): void
    {
        $container = new Container();
        self::assertFalse($container->has(MailerInterface::class));

        $object = $this->getSomeService($container);
        $mailer = $object->fakeMailer();
        self::assertInstanceOf(FakeMailer::class, $mailer);
        self::assertTrue($container->has(MailerInterface::class));
        $mailer2 = $object->fakeMailer();
        self::assertSame($mailer, $mailer2);
    }

    private function getSomeService(Container $container):object
    {
        return new class ($container) {
            use InteractsWithMailer;

            public function __construct(
                private readonly Container $container
            )  {
            }

            public function getContainer(): Container
            {
                return $this->container;
            }
        };
    }
}
