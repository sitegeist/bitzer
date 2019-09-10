<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The SetNewTaskTarget command
 * @Flow\Proxy(false)
 */
final class SetNewTaskTarget
{
    /**
     * @var TaskIdentifier
     */
    private $identifier;

    /**
     * @var UriInterface|null
     */
    private $target;

    public function __construct(TaskIdentifier $identifier, ?UriInterface $target)
    {
        $this->identifier = $identifier;
        $this->target = $target;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }

    public function getTarget(): ?UriInterface
    {
        return $this->target;
    }
}
