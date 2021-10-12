<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 * Class AgentType
 * @package Sitegeist\Bitzer\Domain\Agent
 */
final class AgentType implements \JsonSerializable
{
    const ROLE = 'role';
    const USER = 'user';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, self::getValues())) {
            throw AgentTypeIsInvalid::becauseAgentTypeHasInvalidValue($value, self::getValues());
        }

        $this->value = $value;
    }

    /**
     * @return array<int,string>
     */
    public static function getValues(): array
    {
        return [
            self::ROLE,
            self::USER
        ];
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function role(): self
    {
        return new self(self::ROLE);
    }

    public static function user(): self
    {
        return new self(self::USER);
    }

    public function getIsRole(): bool
    {
        return $this->value === self::ROLE;
    }

    public function getIsUser(): bool
    {
        return $this->value === self::USER;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(AgentType $other): bool
    {
        return $this->getValue() === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
