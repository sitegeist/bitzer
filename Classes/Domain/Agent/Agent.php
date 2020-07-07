<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Agent;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 * Class Agent
 * @package Sitegeist\Bitzer\Domain\Agent
 */
final class Agent
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var AgentType
     */
    private $type;

    /**
     * Agent constructor.
     * @param string $identifier
     * @param AgentType $type
     */
    public function __construct(string $identifier, AgentType $type)
    {
        $this->identifier = $identifier;
        $this->type = $type;
    }

    public static function fromRoleIdentifier(string $identifier): self
    {
        return new self($identifier, AgentType::role());
    }

    public static function fromUserIdentifier(string $identifier): self
    {
        return new self($identifier, AgentType::user());
    }

    public static function fromString(string $string): self
    {
        list($type, $identifier) = explode(':', $string, 2);

        return new self($identifier, AgentType::fromString($type));
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return AgentType
     */
    public function getType(): AgentType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return join(':', [$this->type, $this->identifier]);
    }
}
