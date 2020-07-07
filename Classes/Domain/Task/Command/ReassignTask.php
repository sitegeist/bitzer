<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Agent\Agent;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The ReassignTask command
 * @Flow\Proxy(false)
 */
final class ReassignTask
{
    /**
     * @var TaskIdentifier
     */
    private $identifier;

    /**
     * @var Agent
     */
    private $agent;

    public function __construct(TaskIdentifier $identifier, Agent $agent)
    {
        $this->identifier = $identifier;
        $this->agent = $agent;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }

    public function getAgent(): Agent
    {
        return $this->agent;
    }
}
