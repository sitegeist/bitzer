<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Agent\Agent;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The ScheduleTask command
 * @Flow\Proxy(false)
 */
final class ScheduleTask
{
    private TaskIdentifier $identifier;

    private TaskClassName $className;

    private array $properties;

    private ?\DateTimeImmutable $scheduledTime;

    private Agent $agent;

    private ?NodeAddress $object;

    private UriInterface $target;

    public function __construct(
        TaskIdentifier $identifier,
        TaskClassName $className,
        ?\DateTimeImmutable $scheduledTime,
        Agent $agent,
        ?NodeAddress $object,
        ?UriInterface $target,
        array $properties
    ) {
        $this->identifier = $identifier;
        $this->className = $className;
        $this->scheduledTime = $scheduledTime;
        $this->agent = $agent;
        $this->object = $object;
        $this->target = $target;
        $this->properties = $properties;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }

    public function getClassName(): TaskClassName
    {
        return $this->className;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getScheduledTime(): ?\DateTimeImmutable
    {
        return $this->scheduledTime;
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function getObject(): ?NodeAddress
    {
        return $this->object;
    }

    public function getTarget(): ?UriInterface
    {
        return $this->target;
    }
}
