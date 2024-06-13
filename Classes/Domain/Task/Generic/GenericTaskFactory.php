<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task\Generic;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Object\ObjectRepository;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskFactoryInterface;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Domain\Task\TaskInterface;
use Sitegeist\Bitzer\Domain\Agent\Agent;

/**
 * The generic task factory
 * Creates task objects by using the implementation's constructor
 */
#[Flow\Scope('singleton')]
final class GenericTaskFactory implements TaskFactoryInterface
{
    public function __construct(
        private readonly ObjectRepository $objectRepository
    ) {
    }

    /**
     * @param array<string,mixed> $properties
     */
    final public function createFromRawData(
        TaskIdentifier $identifier,
        TaskClassName $className,
        array $properties,
        \DateTimeImmutable $scheduledTime,
        ActionStatusType $actionStatus,
        Agent $agent,
        ?NodeAddress $object,
        ?UriInterface $target
    ): TaskInterface {
        $object = $object ? $this->objectRepository->findByAddress($object) : null;

        /** @var TaskInterface $task */
        $task = new $className->value(
            $identifier,
            $properties,
            $scheduledTime,
            $actionStatus,
            $agent,
            $object,
            $target
        );

        return $task;
    }
}
