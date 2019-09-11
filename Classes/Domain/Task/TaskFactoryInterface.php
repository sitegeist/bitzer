<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Psr\Http\Message\UriInterface;

/**
 * The interface to be implemented by task factories
 */
interface TaskFactoryInterface
{
    public function createFromRawData(
        TaskIdentifier $identifier,
        TaskClassName $className,
        array $properties,
        \DateTimeImmutable $scheduledTime,
        ActionStatusType $actionStatus,
        string $agent,
        ?NodeAddress $object,
        ?UriInterface $target
    ): TaskInterface;
}
