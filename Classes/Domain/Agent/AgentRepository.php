<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Security\Exception\NoSuchRoleException;
use Neos\Flow\Security\Policy\PolicyService;

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

    public function findAll(): array
    {
        $agentIdentifiers = [];

        foreach ($this->policyService->getRoles(false) as $role) {
            $agentIdentifiers[] = $role->getIdentifier();
        }

        return $agentIdentifiers;
    }

    public function findByIdentifier(string $agentIdentifier): ?string
    {
        try {
            $role = $this->policyService->getRole($agentIdentifier);

            return !$role->isAbstract() ? $agentIdentifier : null;
        } catch (NoSuchRoleException $e) {
            return null;
        }
    }

    /**
     * Returns the currently authenticated agents.
     * Note that a single user can represent multiple agents by their assigned roles.
     *
     * @return array
     * @throws NoSuchRoleException
     * @throws \Neos\Flow\Security\Exception
     */
    public function findCurrent(): array
    {
        $agentIdentifiers = [];

        foreach ($this->securityContext->getRoles() as $role) {
            $agentIdentifiers[] = $role->getIdentifier();
        }

        return $agentIdentifiers;
    }
}
