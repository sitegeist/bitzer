<?php declare(strict_types=1);

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Collections\ArrayCollection;
use Neos\ContentRepository\Domain\Repository\ContentDimensionRepository;
use Neos\ContentRepository\Domain\Service\ContentDimensionPresetSourceInterface;
use Neos\Flow\Http\Uri;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Model\Domain;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;

/**
 * The trait for setting up object fixtures
 */
trait ObjectsTrait
{
    /**
     * @Given /^I have no content dimensions$/
     */
    public function iHaveNoContentDimensions()
    {
        $dimensions = [];

        $contentDimensionRepository = $this->getObjectManager()->get(ContentDimensionRepository::class);
        $contentDimensionRepository->setDimensionsConfiguration($dimensions);

        $contentDimensionPresetSource = $this->getObjectManager()->get(ContentDimensionPresetSourceInterface::class);
        $contentDimensionPresetSource->setConfiguration($dimensions);
    }

    /**
     * @Given /^I have the following sites:$/
     * @param TableNode $siteProperties
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function iHaveTheFollowingSites(TableNode $siteProperties)
    {
        /** @var SiteRepository $siteRepository */
        $siteRepository = $this->getObjectManager()->get(SiteRepository::class);

        /** @var DomainRepository $domainRepository */
        $domainRepository = $this->getObjectManager()->get(DomainRepository::class);

        foreach ($siteProperties->getHash() as $row) {
            $site = new Site($row['nodeName']);
            $site->setName($row['name'] ?? '');
            $site->setSiteResourcesPackageKey($row['siteResourcesPackageKey'] ?? '');
            if (isset($row['domain'])) {
                $uri = new Uri($row['domain']);
                $domain = new Domain();
                $domain->setHostname($uri->getHost());
                $domain->setScheme($uri->getScheme());
                $domain->setSite($site);
                $domainRepository->add($domain);
            }
            $siteRepository->add($site);
        }

        /** @var PersistenceManagerInterface $persistenceManager */
        $persistenceManager = $this->getObjectManager()->get(PersistenceManagerInterface::class);
        $persistenceManager->persistAll();
    }

    abstract protected function getObjectManager(): ObjectManagerInterface;
}
