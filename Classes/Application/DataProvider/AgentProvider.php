<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\DataProvider;

/*
 * This file is part of the Kvh.Shared package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Agent\AgentType;

#[Flow\Scope('singleton')]
final class AgentProvider extends AbstractDataSource implements ProtectedContextAwareInterface
{
    /**
     * @var string
     */
    protected static $identifier = 'sitegeist-bitzer-agent';

    public function __construct(
        private readonly AgentRepository $agentRepository
    ) {
    }

    /**
     * @param array<string,mixed> $arguments
     * @return array<string,mixed>
     */
    public function getData(?NodeInterface $node = null, array $arguments = []): array
    {
        $agents = [];
        foreach ($this->agentRepository->findAll() as $agent) {
            $agents[(string)$agent]['icon'] = $agent->identifier->type === AgentType::TYPE_ROLE ? 'users' : 'user';
            $agents[(string)$agent]['label'] = $agent->label;
        }

        return $agents;
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
