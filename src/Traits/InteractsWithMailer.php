<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Mailer\MailerInterface;
use Spiral\Testing\Mailer\FakeMailer;

trait InteractsWithMailer
{
    public function fakeMailer(): FakeMailer
    {
        $this->getContainer()->bindSingleton(
            MailerInterface::class,
            $mailer = new FakeMailer()
        );

        return $mailer;
    }
}
