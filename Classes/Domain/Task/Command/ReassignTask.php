<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Agent\AgentIdentifier;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The ReassignTask command
 */
#[Flow\Proxy(false)]
final readonly class ReassignTask
{
    public function __construct(
        public TaskIdentifier $identifier,
        public AgentIdentifier $agentId,
    ) {
    }
}
