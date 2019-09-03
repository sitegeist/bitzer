<?php
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;

/**
 * The CreateTask command
 * @Flow\Proxy(false)
 */
final class CreateTask
{
    /**
     * @var TaskIdentifier
     */
    private $identifier;

    /**
     * @var TaskClassName
     */
    private $className;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTimeImmutable
     */
    private $scheduledTime;

    /**
     * @var string
     */
    private $agent;

    /**
     * @var NodeInterface|null
     */
    private $object;

    /**
     * @var UriInterface
     */
    private $target;

    public function __construct(
        TaskIdentifier $identifier,
        TaskClassName $className,
        string $description,
        \DateTimeImmutable $scheduledTime,
        string $agent,
        ?NodeInterface $object,
        ?UriInterface $target
    ) {
        $this->identifier = $identifier;
        $this->className = $className;
        $this->description = $description;
        $this->scheduledTime = $scheduledTime;
        $this->agent = $agent;
        $this->object = $object;
        $this->target = $target;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }

    public function getClassName(): TaskClassName
    {
        return $this->className;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getScheduledTime(): \DateTimeImmutable
    {
        return $this->scheduledTime;
    }

    public function getAgent(): string
    {
        return $this->agent;
    }

    public function getObject(): ?NodeInterface
    {
        return $this->object;
    }

    public function getTarget(): ?UriInterface
    {
        return $this->target;
    }
}
