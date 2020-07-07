<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Security\Exception\NoSuchRoleException;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Neos\Domain\Repository\UserRepository;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Party\Domain\Service\PartyService;
use Neos\Neos\Domain\Model\User;

/**
 * The agent repository
 * @Flow\Scope("singleton")
 */
class AgentRepository
{
    /**
     * @Flow\Inject
     * @var PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var PartyService
     */
    protected $partyService;

    /**
     * @return Agent[]|array
     */
    public function findAll(): array
    {
        $agents = [];

        foreach ($this->policyService->getRoles(false) as $role) {
            $agents[] = Agent::fromRoleIdentifier($role->getIdentifier());
        }

        foreach ($this->userRepository->findAll() as $user) {
            $agents[] = Agent::fromUserIdentifier($this->persistenceManager->getIdentifierByObject($user));
        }
        return $agents;
    }

    /**
     * @param string $agentIdentifier
     * @return Agent|null
     */
    public function findByIdentifier(string $agentIdentifier): ?Agent
    {
        try {
            $role = $this->policyService->getRole($agentIdentifier);
            return !$role->isAbstract() ? Agent::fromRoleIdentifier($agentIdentifier) : null;
        } catch (NoSuchRoleException $e) {
            return  Agent::fromUserIdentifier($agentIdentifier);
        }
    }

    /**
     * Returns the currently authenticated agents.
     * Note that a single user can represent multiple agents by their assigned roles.
     *
     * @return Agent[]|array
     * @throws NoSuchRoleException
     * @throws \Neos\Flow\Security\Exception
     */
    public function findCurrent(): array
    {
        $agentIdentifiers = [];

        foreach ($this->securityContext->getRoles() as $role) {
            if (!$role->isAbstract()) {
                $agentIdentifiers[] = Agent::fromRoleIdentifier($role->getIdentifier());
            }
        }

        $user = $this->partyService->getAssignedPartyOfAccount($this->securityContext->getAccount());
        if ($user instanceof User) {
            $agentIdentifiers[] = Agent::fromUserIdentifier($this->persistenceManager->getIdentifierByObject($user));
        }

        return $agentIdentifiers;
    }
}
