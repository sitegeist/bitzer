<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Sitegeist\Bitzer\Domain\Task\NodeAddress;

/**
 * The exception to be thrown if a requested object does not exist but is supposed to
 */
final class ObjectDoesNotExist extends \DomainException
{
    public static function althoughExpectedForAddress(NodeAddress $nodeAddress): ObjectDoesNotExist
    {
        return new static('No node with identifier ' . $nodeAddress->getNodeAggregateIdentifier() . ' could be found in workspace ' . $nodeAddress->getWorkspaceName() . ' and dimension space point ' .  $nodeAddress->getDimensionSpacePoint() . '.', 1567603391);
    }
}
