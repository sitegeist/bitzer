<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Policy\Role;
use Neos\Neos\Domain\Model\User;

/**
 * @Flow\Proxy(false)
 * Class Agent
 * @package Sitegeist\Bitzer\Domain\Agent
 */
final class Agent
{
    private string $identifier;

    private string $label;

    private AgentType $type;

    public function __construct(string $identifier, string $label, AgentType $type)
    {
        $this->identifier = $identifier;
        $this->label = $label;
        $this->type = $type;
    }

    public static function fromRole(Role $role): self
    {
        return new self(
            $role->getIdentifier(),
            $role->getLabel(),
            AgentType::role()
        );
    }

    public static function fromUser(User $user, string $identifier): self
    {
        return new self(
            $identifier,
            $user->getName()->getFullName(),
            AgentType::user()
        );
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getType(): AgentType
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function equals(Agent $other): bool
    {
        return (
            $this->getIdentifier() === $other->getIdentifier()
            && $this->getType()->equals($other->getType())
        );
    }

    public function getCombinedIdentifier(): string
    {
        return $this->type . ':' . $this->identifier;
    }

    public function __toString(): string
    {
        return $this->getCombinedIdentifier();
    }
}
