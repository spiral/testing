<?php

declare(strict_types=1);

namespace Spiral\Testing\Internal;

use Internal\Container\Attribute\ScopeShared;
use Testo\Common\Messenger;
use Testo\Core\Log\Level;

/**
 * Reports each deprecation once, to the Testo stderr channel when there is a messenger.
 *
 * @internal
 */
#[ScopeShared]
final class Deprecations
{
    /** @var array<non-empty-string, true> */
    private array $reported = [];

    public function __construct(
        private readonly ?Messenger $messenger = null,
    ) {}

    /**
     * @param non-empty-string $message
     */
    public function report(string $message): void
    {
        if (isset($this->reported[$message])) {
            return;
        }

        $this->reported[$message] = true;
        $this->messenger === null
            ? \trigger_error($message, \E_USER_DEPRECATED)
            : $this->messenger->log(Messenger::CHANNEL_STDERR, $message, Level::Warning);
    }
}
