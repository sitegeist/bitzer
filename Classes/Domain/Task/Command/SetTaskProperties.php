<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Command;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The SetTaskProperties command
 * @Flow\Proxy(false)
 */
final class SetTaskProperties
{
    /**
     * @var TaskIdentifier
     */
    private $identifier;

    /**
     * @var array
     */
    private $properties;

    public function __construct(
        TaskIdentifier $identifier,
        array $properties
    ) {
        $this->identifier = $identifier;
        $this->properties = $properties;
    }

    public function getIdentifier(): TaskIdentifier
    {
        return $this->identifier;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}
