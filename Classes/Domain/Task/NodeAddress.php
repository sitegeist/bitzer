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

    public function __construct(
        string $workspaceName,
        DimensionSpacePoint $dimensionSpacePoint,
        NodeAggregateIdentifier $nodeAggregateIdentifier
    ) {
        $this->workspaceName = $workspaceName;
        $this->dimensionSpacePoint = $dimensionSpacePoint;
        $this->nodeAggregateIdentifier = $nodeAggregateIdentifier;
    }

    public static function createFromNode(TraversableNodeInterface $node): NodeAddress
    {
        /** @var NodeInterface $node */
        return new self(
            $node->getContext()->getWorkspaceName(),
            DimensionSpacePoint::fromLegacyDimensionArray($node->getContext()->getDimensions()),
            NodeAggregateIdentifier::fromString($node->getIdentifier())
        );
    }

    public static function createLiveFromNode(TraversableNodeInterface $node): NodeAddress
    {
        /** @var NodeInterface $node */
        return new self(
            'live',
            DimensionSpacePoint::fromLegacyDimensionArray($node->getContext()->getDimensions()),
            NodeAggregateIdentifier::fromString($node->getIdentifier())
        );
    }

    public static function fromJsonString(string $jsonString): self
    {
        return self::createFromArray(\json_decode($jsonString, true));
    }

    public static function createFromArray(array $serialization): self
    {
        return new self(
            $serialization['workspaceName'],
            new DimensionSpacePoint($serialization['dimensionSpacePoint']),
            NodeAggregateIdentifier::fromString($serialization['nodeAggregateIdentifier'])
        );
    }

    public function withWorkspaceName(string $workspaceName): self
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
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
