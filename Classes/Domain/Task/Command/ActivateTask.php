<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The ActivateTask command
 * @Flow\Proxy(false)
 */
final class ActivateTask
{
    /**
     * @var TaskIdentifier
     */
    private $identifier;

    public function __construct(TaskIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }
}
