<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The SetNewTaskObject command
 *
 * @Flow\Proxy(false)
 */
final class SetNewTaskObject
{
    private TaskIdentifier $identifier;

    private ?NodeAddress $object;

    public function __construct(TaskIdentifier $identifier, ?NodeAddress $object)
    {
        $this->identifier = $identifier;
        $this->object = $object;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }

    public function getObject(): ?NodeAddress
    {
        return $this->object;
    }
}
