<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The exception to be thrown if a requested task does not exist but is supposed to
 */
final class TaskDoesNotExist extends \DomainException
{
    public static function althoughExpectedForIdentifier(TaskIdentifier $identifier): self
    {
        return new self('No task with identifier ' . $identifier . ' exists.', 1567600174);
    }
}
