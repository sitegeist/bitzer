<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if a requested agent does not exist but is supposed to
 */
final class AgentDoesNotExist extends \DomainException
{
    public static function althoughExpectedForIdentifier(string $identifier): AgentDoesNotExist
    {
        return new static('No agent with identifier ' . $identifier . ' exists.', 1567602522);
    }
}
