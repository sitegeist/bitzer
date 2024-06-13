<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\Object;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;

/**
 * The labeled object address DTO
 */
#[Flow\Proxy(false)]
final readonly class LabeledObjectAddress implements \Stringable
{
    public function __construct(
        public NodeAddress $identifier,
        public string $label
    ) {
    }

    public function __toString(): string
    {
        return (string)$this->identifier;
    }
}
