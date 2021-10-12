<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Security\Exception\NoSuchRoleException;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Neos\Domain\Repository\UserRepository;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Policy\Role;
use Neos\Neos\Domain\Model\User;
use Neos\Neos\Domain\Service\UserService;

/**
 * The agent domain repository
 *
 * @Flow\Scope("singleton")
 */
final class AgentRepository
{
    private PolicyService $policyService;

    private SecurityContext $securityContext;

    private UserRepository $userRepository;

    private PersistenceManagerInterface $persistenceManager;

    private UserService $userService;

    private Role $bitzerAgentRole;

    public function __construct(
        PolicyService $policyService,
        SecurityContext $securityContext,
        UserRepository $userRepository,
        PersistenceManagerInterface $persistenceManager,
        UserService $userService
    ) {
        $this->policyService = $policyService;
        $this->securityContext = $securityContext;
        $this->userRepository = $userRepository;
        $this->persistenceManager = $persistenceManager;
        $this->userService = $userService;
        $this->bitzerAgentRole = $policyService->getRole('Sitegeist.Bitzer:Agent');
    }

    public function findAll(): Agents
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

        return new Agents($agents);
    }

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
            /** @var User $user */
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
     */
    public function findCurrent(): Agents
    {
        $agents = [];

        $user = $this->userService->getCurrentUser();
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

        return new Agents($agents);
    }

    public function findCurrentByAgentType(AgentType $agentType): ?Agent
    {
        if ($agentType->getIsRole()) {
            foreach ($this->securityContext->getRoles() as $role) {
                if ($this->roleIsEligibleAgent($role)) {
                    return Agent::fromRole($role);
                }
            }
        } elseif ($agentType->getIsUser()) {
            $user = $this->userService->getCurrentUser();
            if ($user instanceof User) {
                if ($this->userIsEligibleAgent($user)) {
                    return Agent::fromUser($user, $this->persistenceManager->getIdentifierByObject($user));
                }
            }
        }

        return null;
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
