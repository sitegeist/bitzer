<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Agent\Agent;
use Sitegeist\Bitzer\Domain\Agent\AgentIdentifier;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The ScheduleTask command
 */
#[Flow\Proxy(false)]
final class ScheduleTask
{
    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(
        public readonly TaskIdentifier $identifier,
        public readonly TaskClassName $className,
        public readonly ?\DateTimeImmutable $scheduledTime,
        public readonly AgentIdentifier $agentId,
        public readonly ?NodeAddress $object,
        public readonly ?UriInterface $target,
        public readonly array $properties
    ) {
    }
}
