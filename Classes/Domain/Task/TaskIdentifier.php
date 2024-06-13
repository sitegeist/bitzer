<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Utility\Algorithms;

/**
 * The task identifier value object
 */
#[Flow\Proxy(false)]
final class TaskIdentifier
{
    public function __construct(
        public readonly string $value
    ) {
    }

    public static function create(): self
    {
        return new self(Algorithms::generateUUID());
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
