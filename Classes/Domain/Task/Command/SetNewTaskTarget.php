<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The SetNewTaskTarget command
 */
#[Flow\Proxy(false)]
final readonly class SetNewTaskTarget
{
    public function __construct(
        public TaskIdentifier $identifier,
        public ?UriInterface $target
    ) {
    }
}
