<?php declare(strict_types=1);
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
    private string $workspaceName;

    private DimensionSpacePoint $dimensionSpacePoint;

    private NodeAggregateIdentifier $nodeAggregateIdentifier;

    public function __construct(
        string $workspaceName,
        DimensionSpacePoint $dimensionSpacePoint,
        NodeAggregateIdentifier $nodeAggregateIdentifier
    ) {
        $this->workspaceName = $workspaceName;
        $this->dimensionSpacePoint = $dimensionSpacePoint;
        $this->nodeAggregateIdentifier = $nodeAggregateIdentifier;
    }

    public static function createFromNode(TraversableNodeInterface $node): self
    {
        /** @var NodeInterface $node */
        return new self(
            $node->getContext()->getWorkspaceName(),
            DimensionSpacePoint::fromLegacyDimensionArray($node->getContext()->getDimensions()),
            NodeAggregateIdentifier::fromString($node->getIdentifier())
        );
    }

    public static function createLiveFromNode(TraversableNodeInterface $node): self
    {
        /** @var NodeInterface $node */
        return new self(
            'live',
            DimensionSpacePoint::fromLegacyDimensionArray($node->getContext()->getDimensions()),
            NodeAggregateIdentifier::fromString($node->getIdentifier())
        );
    }

    /**
     * @param array<string,mixed> $serialization
     */
    public static function createFromArray(array $serialization): self
    {
        return new self(
            $serialization['workspaceName'],
            new DimensionSpacePoint($serialization['dimensionSpacePoint']),
            NodeAggregateIdentifier::fromString($serialization['nodeAggregateIdentifier'])
        );
    }

    public function withWorkspaceName(string $workspaceName): NodeAddress
    {
        return new self(
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

    /**
     * @return array<string,mixed>
     */
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
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
