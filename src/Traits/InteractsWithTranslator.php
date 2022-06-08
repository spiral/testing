<?php

declare(strict_types=1);

namespace Spiral\Testing\Traits;

use Spiral\Translator\TranslatorInterface;

trait InteractsWithTranslator
{
    final public function getTranslator(): TranslatorInterface
    {
        return $this->getContainer()->get(TranslatorInterface::class);
    }

    public function withLocale(string $locale): self
    {
        $this->getTranslator()->setLocale($locale);

        return $this;
    }
}
