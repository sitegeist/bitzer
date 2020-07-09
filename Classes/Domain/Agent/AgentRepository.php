<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Security\Exception\NoSuchRoleException;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Flow\Security\AccountRepository;
use Neos\Neos\Domain\Repository\UserRepository;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Party\Domain\Service\PartyService;
use Neos\Flow\Security\Policy\Role;
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
     * @Flow\Inject
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @var Role
     */
    protected $bitzerAgentRole;

    public function initializeObject()
    {
        $this->bitzerAgentRole = $this->policyService->getRole('Sitegeist.Bitzer:Agent');
    }

    /**
     * @return Agent[]|array
     */
    public function findAll(): array
    {
        $agents = [];

        foreach ($this->policyService->getRoles(false) as $role) {
            if ($this->roleIsEligibleAgent($role)) {
                $agents[] = Agent::fromRole($role);
            }
        }

        foreach ($this->userRepository->findAll() as $user) {
            if ($this->userIsEligibleAgent($user)) {
                $agents[] = Agent::fromUser($user, $this->persistenceManager->getIdentifierByObject($user));
            }
        }
        return $agents;
    }

    /**
     * @param string $string
     * @return Agent|null
     */
    public function findByString(string $string): ?Agent
    {
        if (strpos($string, ':') === false) {
            return null;
        }

        list($type, $identifier) = explode(':', $string, 2);

        return $this->findByTypeAndIdentifier(
            AgentType::fromString($type),
            $identifier
        );
    }

    /**
     * @param AgentType $type
     * @param string $identifier
     * @return Agent|null
     */
    public function findByTypeAndIdentifier(AgentType $type, string $identifier): ?Agent
    {
        if ($type->getIsRole()) {
            try {
                $role = $this->policyService->getRole($identifier);
                if ($this->roleIsEligibleAgent($role)) {
                    return Agent::fromRole($role);
                }
            } catch (NoSuchRoleException $e) {
                return null;
            }
        } elseif ($type->getIsUser()) {
            $user = $this->userRepository->findByIdentifier($identifier);
            if ($user) {
                if ($this->userIsEligibleAgent($user)) {
                    return Agent::fromUser($user, $identifier);
                }
            }
        }

        return null;
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
        $agents = [];

        $user = $this->partyService->getAssignedPartyOfAccount($this->securityContext->getAccount());
        if ($user instanceof User) {
            if ($this->userIsEligibleAgent($user)) {
                $agents[] = Agent::fromUser($user, $this->persistenceManager->getIdentifierByObject($user));
            }
        }

        foreach ($this->securityContext->getRoles() as $role) {
            if ($this->roleIsEligibleAgent($role)) {
                $agents[] = Agent::fromRole($role);
            }
        }

        return $agents;
    }

    /**
     * @param AgentType $agentType
     * @return Agent|null
     */
    public function findCurrentByAgentType(AgentType $agentType): ?Agent
    {
        if ($agentType->getIsRole()) {
            foreach ($this->securityContext->getRoles() as $role) {
                if ($this->roleIsEligibleAgent($role)) {
                    return Agent::fromRole($role);
                }
            }
        } elseif ($agentType->getIsUser()) {
            $user = $this->partyService->getAssignedPartyOfAccount($this->securityContext->getAccount());
            if ($user instanceof User) {
                if ($this->userIsEligibleAgent($user)) {
                    return Agent::fromUser($user, $this->persistenceManager->getIdentifierByObject($user));
                }
            }
        }
    }

    public function roleIsEligibleAgent(Role $role): bool
    {
        if ($role->getIdentifier() === $this->bitzerAgentRole->getIdentifier()) {
            return true;
        } else {
            foreach ($role->getAllParentRoles() as $parentRole) {
                if ($this->roleIsEligibleAgent($parentRole)) {
                    return true;
                }
            }

            return false;
        }
    }

    public function userIsEligibleAgent(User $user): bool
    {
        foreach ($user->getAccounts() as $account) {
            foreach ($account->getRoles() as $role) {
                if ($this->roleIsEligibleAgent($role)) {
                    return true;
                }
            }
        }

        return false;
    }
}
