<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * The interface to be implemented by tasks, according to https://schema.org/ScheduleAction
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
        ?string $target
    ): TaskInterface;
}
