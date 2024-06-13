<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class AgentIdentifier implements \Stringable
{
    public function __construct(
        public readonly AgentType $type,
        public readonly string $identifier
    ) {
    }

    public static function fromString(string $string): self
    {
        list($type, $identifier) = explode(':', $string, 2);

        return new self(
            AgentType::from($type),
            $identifier
        );
    }

    public function equals(AgentIdentifier $other): bool
    {
        return $this->identifier === $other->identifier
            && $this->type === $other->type;
    }

    public function toString(): string
    {
        return $this->type->value . ':' . $this->identifier;
    }

    public function getString(): string
    {
        return $this->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
