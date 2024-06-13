<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Object;

use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Infrastructure\ContentContextFactory;

/**
 * The object repository. Don't call it content graph!
 */
#[Flow\Scope('singleton')]
final class ObjectRepository
{
    public function __construct(
        private readonly ContentContextFactory $contentContextFactory
    ) {
    }

    public function findByAddress(NodeAddress $nodeAddress): ?TraversableNodeInterface
    {
        $contentContext = $this->contentContextFactory->createContentContext($nodeAddress);

        /** @var TraversableNodeInterface|null $object */
        $object = $contentContext->getNodeByIdentifier((string)$nodeAddress->nodeAggregateIdentifier);

        return $object;
    }
}
