<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Policy\Role;
use Neos\Neos\Domain\Model\User;

#[Flow\Proxy(false)]
final readonly class Agent
{
    public function __construct(
        public AgentIdentifier $identifier,
        public string $label
    ) {
    }

    public static function fromRole(Role $role): self
    {
        return new self(
            new AgentIdentifier(
                AgentType::TYPE_ROLE,
                $role->getIdentifier()
            ),
            $role->getLabel(),
        );
    }

    public static function fromUser(User $user, string $identifier): self
    {
        return new self(
            new AgentIdentifier(
                AgentType::TYPE_USER,
                $identifier
            ),
            $user->getName()->getFullName(),
        );
    }

    public function equals(Agent $other): bool
    {
        return $this->identifier->equals($other->identifier);
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
