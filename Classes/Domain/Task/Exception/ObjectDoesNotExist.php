<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Sitegeist\Bitzer\Domain\Task\NodeAddress;

/**
 * The exception to be thrown if a requested object does not exist but is supposed to
 */
final class ObjectDoesNotExist extends \DomainException
{
    public static function althoughExpectedForAddress(NodeAddress $nodeAddress): self
    {
        return new self('No node with identifier ' . $nodeAddress->nodeAggregateIdentifier . ' could be found in workspace ' . $nodeAddress->workspaceName . ' and dimension space point ' .  $nodeAddress->dimensionSpacePoint . '.', 1567603391);
    }
}
