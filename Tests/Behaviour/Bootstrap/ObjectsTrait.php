<?php
declare(strict_types=1);

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Behat\Gherkin\Node\TableNode;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\SiteRepository;

/**
 * The trait for setting up object fixtures
 */
trait ObjectsTrait
{
    /**
     * @Given /^I have the following sites:$/
     * @param TableNode $siteProperties
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function iHaveTheFollowingSites(TableNode $siteProperties)
    {
        /** @var SiteRepository $siteRepository */
        $siteRepository = $this->getObjectManager()->get(SiteRepository::class);

        foreach ($siteProperties->getHash() as $row) {
            $site = new Site($row['nodeName']);
            $site->setName($row['name'] ?? '');
            $site->setSiteResourcesPackageKey($row['siteResourcesPackageKey'] ?? '');
            $siteRepository->add($site);
        }

        /** @var PersistenceManagerInterface $persistenceManager */
        $persistenceManager = $this->getObjectManager()->get(PersistenceManagerInterface::class);
        $persistenceManager->persistAll();
    }

    abstract protected function getObjectManager(): ObjectManagerInterface;
}
