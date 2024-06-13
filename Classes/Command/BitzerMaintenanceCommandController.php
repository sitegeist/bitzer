<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Command;

use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * The command line endpoint for sending commands to Bitzer
 */
#[Flow\Scope('singleton')]
final class BitzerMaintenanceCommandController extends CommandController
{
    public function __construct(
        private readonly NodeTypeManager $nodeTypeManager,
        private readonly NodeDataRepository $nodeDataRepository,
    ) {
        parent::__construct();
    }

    public function updateAgentPropertiesCommand(): void
    {
        $agentNodeTypes = $this->nodeTypeManager->getSubNodeTypes('Sitegeist.Bitzer:Mixin.Setting.Agent', false);
        $query = $this->nodeDataRepository->createQuery();
        /** @var NodeData[] $nodes */
        $nodes = $query->matching(
            $query->in('nodeType', array_map(fn (NodeType $nodeType): string => $nodeType->getName(), $agentNodeTypes))
        )->execute();

        $this->outputLine('Migrating agent nodes...');
        $this->output->progressStart(count($nodes));

        foreach ($nodes as $node) {
            $taskAgents = [];
            if ($node->hasProperty('bitzerTaskAgent')) {
                $taskAgents = [$node->getProperty('bitzerTaskAgent')];
                $node->removeProperty('bitzerTaskAgent');
            }
            $node->setProperty('bitzerTaskAgents', $taskAgents);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->output->outputLine('');
        $this->output->outputLine('Done.');
    }
}
