<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Generic;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Uri;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskFactoryInterface;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Domain\Task\TaskInterface;

/**
 * The generic task factory
 *
 * Creates task objects by using the implementation's constructor
 * @Flow\Proxy(false)
 */
final class GenericTaskFactory implements TaskFactoryInterface
{
    final public function createFromRawData(
        TaskIdentifier $identifier,
        TaskClassName $className,
        array $properties,
        \DateTimeImmutable $scheduledTime,
        ActionStatusType $actionStatus,
        string $agent,
        ?NodeInterface $object,
        ?UriInterface $target
    ): TaskInterface {
        $classIdentifier = (string)$className;
        return new $classIdentifier(
            $identifier,
            $properties,
            $scheduledTime,
            $actionStatus,
            $agent,
            $object,
            $target
        );
    }
}
