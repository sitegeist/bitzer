<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The exception to be thrown if a requested task exists but is not supposed to
 */
#[Flow\Proxy(false)]
final class TaskDoesExist extends \DomainException
{
    public static function althoughNotExpectedForIdentifier(TaskIdentifier $identifier): self
    {
        return new self('Task with identifier ' . $identifier . ' already exists.', 1567600184);
    }
}
