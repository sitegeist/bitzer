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

    public static function fromRawData(array $rawData): self
    {
        return new self($rawData['agent'], AgentType::fromInteger((int) $rawData['agenttype']));
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

    public function __toString()
    {
        return $this->identifier;
    }
}
