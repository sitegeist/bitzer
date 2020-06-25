<?php
namespace Sitegeist\Bitzer\Infrastructure;

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\Flow\Annotations as Flow;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Response;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Http;
use Neos\Neos\Service\LinkingService;
use Neos\Flow\Mvc;
use Sitegeist\Bitzer\Domain\Object\ObjectRepository;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;


/**
 * The URI service
 * @Flow\Scope("singleton")
 */
class UriService
{
    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @Flow\Inject
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @param TraversableNodeInterface $object
     * @return Uri
     * @throws Mvc\Routing\Exception\MissingActionNameException
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Neos\Exception
     */
    public function findUriByObject(TraversableNodeInterface $object): Uri
    {
        return new Uri($this->linkingService->createNodeUri($this->getControllerContext(), $object, null, null, true));
    }

    /**
     * @param NodeAddress $objectAddress
     * @return Uri
     * @throws Mvc\Routing\Exception\MissingActionNameException
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Neos\Exception
     */
    public function findUriByAddress(NodeAddress $objectAddress): Uri
    {
        $object = $this->objectRepository->findByAddress($objectAddress);

        return $this->findUriByObject($object);
    }

    protected function getControllerContext(): ControllerContext
    {
        if (is_null($this->controllerContext)) {
            $httpRequest = ServerRequest::fromGlobals();
            $request = ActionRequest::fromHttpRequest($httpRequest);
            $uriBuilder = new Mvc\Routing\UriBuilder();
            $uriBuilder->setRequest($request);
            $this->controllerContext = new Mvc\Controller\ControllerContext(
                $request,
                new ActionResponse(),
                new Mvc\Controller\Arguments(),
                $uriBuilder
            );
        }
        return $this->controllerContext;
    }
}
