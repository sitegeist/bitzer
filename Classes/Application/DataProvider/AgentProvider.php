<?php
namespace Sitegeist\Bitzer\Application\DataProvider;

/*
 * This file is part of the Kvh.Shared package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;

class AgentProvider extends AbstractDataSource implements ProtectedContextAwareInterface
{
    /**
     * @var string
     */
    protected static $identifier = 'sitegeist-bitzer-agent';

    /**
     * @Flow\Inject
     * @var AgentRepository
     */
    protected $agentRepository;

    public function getData(NodeInterface $node = null, array $arguments = []): array
    {
        $agents = [];
        foreach ($this->agentRepository->findAll() as $agent) {
            $agents[(string) $agent]['icon'] = $agent->getType()->getIsRole() ? 'users' : 'user';
            $agents[(string) $agent]['label'] = $agent->getLabel();
        }

        return $agents;
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
