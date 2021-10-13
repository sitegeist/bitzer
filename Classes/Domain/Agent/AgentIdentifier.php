<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
final class AgentIdentifier implements ProtectedContextAwareInterface
{
    private AgentType $type;

    private string $identifier;

    public function __construct(AgentType $type, string $identifier)
    {
        $this->type = $type;
        $this->identifier = $identifier;
    }

    public static function fromString(string $string): self
    {
        list($type, $identifier) = explode(':', $string, 2);

        return new self(
            AgentType::fromString($type),
            $identifier
        );
    }

    public function getType(): AgentType
    {
        return $this->type;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function equals(AgentIdentifier $other): bool
    {
        return $this->getIdentifier() === $other->getIdentifier()
            && $this->getType()->equals($other->getType());
    }

    public function toString(): string
    {
        return $this->type . ':' . $this->identifier;
    }

    public function getString(): string
    {
        return $this->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param string $methodName
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
