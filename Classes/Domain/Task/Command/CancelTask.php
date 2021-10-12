<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The CancelTask command
 *
 * @Flow\Proxy(false)
 */
final class CancelTask
{
    private TaskIdentifier $identifier;

    public function __construct(TaskIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }
}
