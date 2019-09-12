<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\ContentRepository\DimensionSpace\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\NodeAggregate\NodeAggregateIdentifier;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;

/**
 * The node address value object
 *
 * To be replaced by the actual node address from the new content repository
 * @Flow\Proxy(false)
 */
final class NodeAddress implements \JsonSerializable, ProtectedContextAwareInterface
{
    /**
     * @var string
     */
    private $workspaceName;

    /**
     * @var DimensionSpacePoint
     */
    private $dimensionSpacePoint;

    /**
     * @var NodeAggregateIdentifier
     */
    private $nodeAggregateIdentifier;

    public function __construct(string $workspaceName, DimensionSpacePoint $dimensionSpacePoint, NodeAggregateIdentifier $nodeAggregateIdentifier)
    {
        $this->workspaceName = $workspaceName;
        $this->dimensionSpacePoint = $dimensionSpacePoint;
        $this->nodeAggregateIdentifier = $nodeAggregateIdentifier;
    }

    public static function fromNode(TraversableNodeInterface $node): NodeAddress
    {
        /** @var NodeInterface $node */
        return new static(
            $node->getContext()->getWorkspaceName(),
            DimensionSpacePoint::fromLegacyDimensionArray($node->getContext()->getDimensions()),
            NodeAggregateIdentifier::fromString($node->getIdentifier())
        );
    }

    public static function fromArray(array $serialization): NodeAddress
    {
        return new static(
            $serialization['workspaceName'],
            new DimensionSpacePoint($serialization['dimensionSpacePoint']),
            NodeAggregateIdentifier::fromString($serialization['nodeAggregateIdentifier'])
        );
    }

    public function withWorkspaceName(string $workspaceName): NodeAddress
    {
        return new static(
            $workspaceName,
            $this->dimensionSpacePoint,
            $this->nodeAggregateIdentifier
        );
    }

    public function getWorkspaceName(): string
    {
        return $this->workspaceName;
    }

    public function getDimensionSpacePoint(): DimensionSpacePoint
    {
        return $this->dimensionSpacePoint;
    }

    public function getNodeAggregateIdentifier(): NodeAggregateIdentifier
    {
        return $this->nodeAggregateIdentifier;
    }

    public function jsonSerialize(): array
    {
        return [
            'workspaceName' => $this->workspaceName,
            'dimensionSpacePoint' => $this->dimensionSpacePoint,
            'nodeAggregateIdentifier' => $this->nodeAggregateIdentifier
        ];
    }

    public function equals(NodeAddress $other): bool
    {
        return $this->jsonSerialize() == $other->jsonSerialize();
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
