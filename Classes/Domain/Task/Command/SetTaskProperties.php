<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The SetTaskProperties command
 */
#[Flow\Proxy(false)]
final readonly class SetTaskProperties
{
    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(
        public TaskIdentifier $identifier,
        public array $properties
    ) {
    }
}
