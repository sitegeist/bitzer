<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\Object;

use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\ContentRepository\Domain\Service\ContextFactoryInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\ContentContext;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;

/**
 * The object provider
 */
class ObjectProvider implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var ContextFactoryInterface
     */
    protected $contentContextFactory;

    public function getObjects(): array
    {
        $flowQuery = new FlowQuery([$this->getContentContext()->getCurrentSiteNode()]);

        $objects = [];
        foreach ($flowQuery->find('[instanceof Neos.Neos:Document]')->get() as $document) {
            /** @var TraversableNodeInterface $document */
            $objects[] = new LabeledObjectAddress(NodeAddress::fromNode($document), $document->getLabel());
        }

        return $objects;
    }

    public function getAddress(?TraversableNodeInterface $object): ?NodeAddress
    {
        return $object ? NodeAddress::fromNode($object) : null;
    }

    /**
     * @todo add dimension support
     * @return ContentContext
     */
    private function getContentContext(): ContentContext
    {
        /** @var ContentContext $contentContext */
        $contentContext = $this->contentContextFactory->create([]);

        return $contentContext;
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
