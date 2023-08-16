<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Mailer\MailerInterface;
use Spiral\Testing\Mailer\FakeMailer;

trait InteractsWithMailer
{
    public function fakeMailer(): FakeMailer
    {
        $container = $this->getContainer();
        if ($container->has(MailerInterface::class)) {
            $mailer = $container->get(MailerInterface::class);
            if ($mailer instanceof FakeMailer) {
                return $mailer;
            }
        }

        $container->removeBinding(MailerInterface::class);
        $container->bindSingleton(
            MailerInterface::class,
            $mailer = new FakeMailer()
        );

        return $mailer;
    }
}
