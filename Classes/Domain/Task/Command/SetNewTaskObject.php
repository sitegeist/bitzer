<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The SetNewTaskObject command
 */
#[Flow\Proxy(false)]
final class SetNewTaskObject
{
    public function __construct(
        public readonly TaskIdentifier $identifier,
        public readonly ?NodeAddress $object
    ) {
    }
}
