<?php

declare(strict_types=1);

namespace Spiral\Testing\Session;

use Spiral\Session\SessionInterface;
use Spiral\Session\SessionSectionInterface;

final class FakeSession implements SessionInterface
{
    private bool $started = false;
    private ?string $id = null;

    public function __construct(
        private array $data,
        private readonly string $clientSignature,
        private readonly int $lifetime = 3600,
         ?string $id = null
    ) {
        if (! empty($id) && $this->validID($id)) {
            $this->id = $id;
        }
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function resume(): void
    {
        if ($this->isStarted()) {
            return;
        }

        if (empty($this->id)) {
            //Sign newly created session
            $this->data['_CLIENT_SIGNATURE'] = $this->clientSignature;
            $this->data['_CREATED'] = time();
        }

        $this->id = 'session-'.time();

        $this->started = true;
    }

    public function getID(): ?string
    {
        return $this->id;
    }

    public function regenerateID(): SessionInterface
    {
        $this->resume();

        $this->data['_CREATED'] = time();
        $this->id = 'session-'.time();

        $this->resume();

        return $this;
    }

    public function commit(): bool
    {
        if (! $this->isStarted()) {
            return false;
        }

        $this->started = false;

        return true;
    }

    public function abort(): bool
    {
        if (! $this->isStarted()) {
            return false;
        }

        $this->data = [];

        $this->started = false;

        return true;
    }

    public function destroy(): bool
    {
        $this->resume();

        $this->data = [
            '_CLIENT_SIGNATURE' => $this->clientSignature,
            '_CREATED' => time(),
        ];

        return $this->commit();
    }

    public function getSection(string $name = null): SessionSectionInterface
    {
        return new FakeSessionSection(
            $name ?? '_DEFAULT', $this->data[$name] ?? []
        );
    }

    /**
     * Check if given session ID valid.
     */
    private function validID(string $id): bool
    {
        return preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $id) !== false;
    }
}
