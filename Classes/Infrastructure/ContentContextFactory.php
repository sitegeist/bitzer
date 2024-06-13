<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Infrastructure;

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Neos\ContentRepository\Domain\Service\Context;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;

/**
 * The content context factory for task objects
 */
#[Flow\Scope('singleton')]
class ContentContextFactory
{
    public function __construct(
        private readonly ContextFactoryInterface $coreContentContextFactory,
        private readonly ContentDimensionPresetSourceInterface $contentDimensionPresetSource,
    ) {
    }

    public function createContentContext(NodeAddress $nodeAddress): Context
    {
        $presets = $this->contentDimensionPresetSource->getAllPresets();
        $contextDimensions = [];
        foreach ($nodeAddress->dimensionSpacePoint->getCoordinates() as $dimensionName => $dimensionValue) {
            $contextDimensions[$dimensionName] = $presets[$dimensionName]['presets'][$dimensionValue]['values'];
        }
        $contentContext = $this->coreContentContextFactory->create([
            'workspaceName' => $nodeAddress->workspaceName,
            'dimensions' => $contextDimensions,
            'targetDimensions' => $nodeAddress->dimensionSpacePoint->getCoordinates(),
            'invisibleContentShown' => true,
            'removedContentShown' => false,
            'inaccessibleContentShown' => true
        ]);

        return $contentContext;
    }
}
