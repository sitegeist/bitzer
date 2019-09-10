<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The RescheduleTask command
 * @Flow\Proxy(false)
 */
final class RescheduleTask
{
    /**
     * @var TaskIdentifier
     */
    private $identifier;

    /**
     * @var ?\DateTimeImmutable
     */
    private $scheduledTime;

    public function __construct(TaskIdentifier $identifier, ?\DateTimeImmutable $scheduledTime)
    {
        $this->identifier = $identifier;
        $this->scheduledTime = $scheduledTime;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }

    public function getScheduledTime(): ?\DateTimeImmutable
    {
        return $this->scheduledTime;
    }
}
