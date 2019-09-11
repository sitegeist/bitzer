<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The exception to be thrown if a requested task exists but is not supposed to
 */
final class TaskDoesExist extends \DomainException
{
    public static function althoughNotExpectedForIdentifier(TaskIdentifier $identifier): TaskDoesExist
    {
        return new static('Task with identifier ' . $identifier . ' already exists.', 1567600184);
    }
}
