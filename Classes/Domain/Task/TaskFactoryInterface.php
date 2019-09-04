<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Psr\Http\Message\UriInterface;

/**
 * The interface to be implemented by task factories
 */
interface TaskFactoryInterface
{
    public function createFromRawData(
        TaskIdentifier $identifier,
        TaskClassName $className,
        string $description,
        \DateTimeImmutable $scheduledTime,
        ActionStatusType $actionStatus,
        string $agent,
        ?NodeInterface $object,
        ?UriInterface $target
    ): TaskInterface;
}
