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
    const ROLE = 1;
    const USER = 2;

    /**
     * @var int
     */
    private $value;

    /**
     * AgentType constructor.
     * @param int $value
     */
    public function __construct(int $value)
    {
        if (!in_array($value, self::getValues())) {
            throw AgentTypeIsInvalid::becauseAgentTypeHasInvalidValue($value, self::getValues());
        }

        $this->value = $value;
    }

    /**
     * @return array|int[]
     */
    public static function getValues(): array
    {
        return [
            self::ROLE,
            self::USER
        ];
    }

    public static function fromInteger(int $value): self
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

    public function getValue(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
