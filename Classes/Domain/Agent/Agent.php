<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Policy\Role;
use Neos\Neos\Domain\Model\User;

/**
 * @Flow\Proxy(false)
 */
final class Agent
{
    private AgentIdentifier $identifier;

    private string $label;

    public function __construct(AgentIdentifier $identifier, string $label)
    {
        $this->identifier = $identifier;
        $this->label = $label;
    }

    public static function fromRole(Role $role): self
    {
        return new self(
            new AgentIdentifier(
                AgentType::role(),
                $role->getIdentifier()
            ),
            $role->getLabel(),
        );
    }

    public static function fromUser(User $user, string $identifier): self
    {
        return new self(
            new AgentIdentifier(
                AgentType::user(),
                $identifier
            ),
            $user->getName()->getFullName(),
        );
    }

    public function getIdentifier(): AgentIdentifier
    {
        return $this->identifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function equals(Agent $other): bool
    {
        return $this->identifier->equals($other->getIdentifier());
    }

    public function toString(): string
    {
        return $this->identifier->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
