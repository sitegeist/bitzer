<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\Object;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;

/**
 * The labeled object address DTO
 */
final class LabeledObjectAddress
{
    /**
     * @var NodeAddress
     */
    private $identifier;

    /**
     * @var string
     */
    private $label;

    public function __construct(NodeAddress $identifier, string $label)
    {
        $this->identifier = $identifier;
        $this->label = $label;
    }

    public function getIdentifier(): NodeAddress
    {
        return $this->identifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function __toString(): string
    {
        return (string)$this->identifier;
    }
}
