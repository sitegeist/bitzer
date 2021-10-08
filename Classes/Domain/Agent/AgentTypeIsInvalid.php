<?php declare(strict_types=1);
namespace  Sitegeist\Bitzer\Domain\Agent;

/*
 * This file is part of the Kvh.KvhhNet.MedicalStaff package.
 */

use Neos\Flow\Annotations as Flow;

/**
 * The exception to be thrown if an invalid period is tried to be initialized
 * @Flow\Proxy(false)
 */
final class AgentTypeIsInvalid extends \DomainException
{
    public static function becauseAgentTypeHasInvalidValue(string $attemptedValue, array $validValues): self
    {
        return new self('"' . $attemptedValue . '" is no valid value for AgentType, must be one of '.join(', ', $validValues), 1591622975);
    }
}
