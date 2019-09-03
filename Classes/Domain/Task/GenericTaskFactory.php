<?php
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Uri;
use Psr\Http\Message\UriInterface;

/**
 * The generic task factory
 *
 * Creates task objects by using the implementation's constructor
 *
 * @Flow\Proxy(false)
 */
class GenericTaskFactory implements TaskFactoryInterface
{
    final public function createFromRawData(
        TaskIdentifier $identifier,
        TaskClassName $className,
        string $description,
        \DateTimeImmutable $scheduledTime,
        ActionStatusType $actionStatus,
        string $agent,
        ?NodeInterface $object,
        ?string $target
    ): TaskInterface {
        return new $className(
            $identifier,
            $description,
            $scheduledTime,
            $actionStatus,
            $agent,
            $object,
            $target
        );
    }
}
