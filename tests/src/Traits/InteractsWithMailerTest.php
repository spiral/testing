<?php

declare(strict_types=1);

namespace Spiral\Testing\Tests\Traits;

use Spiral\Core\Container;
use Spiral\Mailer\MailerInterface;
use Spiral\Testing\Mailer\FakeMailer;
use Spiral\Testing\Traits\InteractsWithMailer;
use Testo\Assert;
use Testo\Test;

/**
 * @coversDefaultClass InteractsWithMailer
 */
#[Test]
final class InteractsWithMailerTest
{
    public function test(): void
    {
        $container = new Container();
        Assert::false($container->has(MailerInterface::class));
        $object = $this->getSomeService($container);
        $mailer = $object->fakeMailer();
        Assert::instanceOf($mailer, FakeMailer::class);
        Assert::true($container->has(MailerInterface::class));
        $mailer2 = $object->fakeMailer();
        Assert::same($mailer2, $mailer);
    }

    private function getSomeService(Container $container): object
    {
        return new class($container) {
            use InteractsWithMailer;

            public function __construct(
                private readonly Container $container,
            ) {}

            public function getContainer(): Container
            {
                return $this->container;
            }
        };
    }
}
