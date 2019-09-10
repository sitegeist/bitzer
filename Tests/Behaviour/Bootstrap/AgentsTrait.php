<?php
declare(strict_types=1);

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Flow\Security\Policy\Role;
use Neos\Utility\ObjectAccess;
use Symfony\Component\Yaml\Yaml;

/**
 * The trait for setting up agent fixtures
 */
trait AgentsTrait
{
    /**
     * @var array
     */
    protected $rolesBackup;

    /**
     * @AfterScenario @fixtures
     */
    public function resetCustomRoles()
    {
        if (!is_null($this->rolesBackup)) {
            /** @var PolicyService $policyService */
            $policyService = $this->getObjectManager()->get(PolicyService::class);
            ObjectAccess::setProperty($policyService, 'roles', $this->rolesBackup, true);
        }
    }

    /**
     * @Given /^I have the following additional agents:$/
     */
    public function iHaveTheFollowingAdditionalAgents($agentConfiguration)
    {
        /** @var PolicyService $policyService */
        $policyService = $this->getObjectManager()->get(PolicyService::class);
        $roles = $policyService->getRoles(true);
        $this->rolesBackup = $roles;

        $additionalRoleConfiguration = Yaml::parse($agentConfiguration->getRaw());
        foreach ($additionalRoleConfiguration as $roleIdentifier => $roleConfiguration) {
            $parentRoles = [];
            if (isset($roleConfiguration['parentRoles'])) {
                foreach ($roleConfiguration['parentRoles'] as $parentRoleIdentifier) {
                    $parentRoles[$parentRoleIdentifier] = $roles[$parentRoleIdentifier];
                }
            }
            $roles[$roleIdentifier] = new Role($roleIdentifier, $parentRoles);
        }
        ObjectAccess::setProperty($policyService, 'roles', $roles, true);
    }

    abstract protected function getObjectManager(): ObjectManagerInterface;
}
