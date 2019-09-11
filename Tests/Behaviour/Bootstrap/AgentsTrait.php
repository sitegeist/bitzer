<?php
declare(strict_types=1);

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Neos\Flow\Http\Request;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Authentication\AuthenticationManagerInterface;
use Neos\Flow\Security\Authentication\Provider\TestingProvider;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Flow\Security\Policy\Role;
use Neos\Neos\Domain\Service\UserService;
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

    /**
     * @Given /^I am authenticated as existing user "([^"]*)"/
     * @param string $userName
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Flow\Security\Exception\AuthenticationRequiredException
     * @throws \Neos\Flow\Security\Exception\NoTokensAuthenticatedException
     * @throws \Neos\Utility\Exception\PropertyNotAccessibleException
     */
    public function iAmAuthenticatedAsExistingUser(string $userName)
    {
        /** @var UserService $userDomainService */
        $userDomainService = $this->getObjectManager()->get(UserService::class);
        $password = 'secret';
        $user = $userDomainService->createUser($userName, $password, 'GivenName', 'LastName');

        /** @var PersistenceManagerInterface $persistenceManager */
        $persistenceManager = $this->getObjectManager()->get(PersistenceManagerInterface::class);
        $persistenceManager->persistAll();

        $authenticationRequest = new ActionRequest(new Request(
            [],
            [
                '__authentication' => [
                    'Neos' => [
                        'Flow' => [
                            'Security' => [
                                'Authentication' => [
                                    'Token' => [
                                        'UsernamePassword' => [
                                            'username' => $userName,
                                            'password' => $password
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [],
            []
        ));

        /** @var Context $securityContext */
        $securityContext = $this->getObjectManager()->get(Context::class);
        $securityContext->setRequest($authenticationRequest);
        $securityContext->initialize();

        /** @var TokenInterface[] $activeTokens */
        $activeTokens = ObjectAccess::getProperty($securityContext, 'activeTokens', true);
        $inactiveTokens = ObjectAccess::getProperty($securityContext, 'inactiveTokens', true);
        $activeTokens['Neos.Neos:Backend'] = $inactiveTokens['Neos.Neos:Backend'];
        $activeTokens['Neos.Neos:Backend']->setAccount($user->getAccounts()->first());
        $activeTokens['Neos.Neos:Backend']->setAuthenticationStatus(TokenInterface::AUTHENTICATION_SUCCESSFUL);
        ObjectAccess::setProperty($securityContext, 'activeTokens', $activeTokens, true);

        /** @var AuthenticationManagerInterface $authenticationManager */
        $authenticationManager = $this->getObjectManager()->get(AuthenticationManagerInterface::class);
        $authenticationManager->authenticate();
    }

    abstract protected function getObjectManager(): ObjectManagerInterface;
}
