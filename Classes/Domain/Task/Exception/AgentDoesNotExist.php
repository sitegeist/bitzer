<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if a requested agent does not exist but is supposed to
 */
#[Flow\Proxy(false)]
final class AgentDoesNotExist extends \DomainException
{
    public static function althoughExpectedForIdentifier(string $identifier): self
    {
        return new self('No agent with identifier ' . $identifier . ' exists.', 1567602522);
    }
}
