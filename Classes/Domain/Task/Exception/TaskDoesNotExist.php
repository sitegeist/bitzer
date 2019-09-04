<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if a requested task does not exist but is supposed to
 */
final class TaskDoesNotExist extends \DomainException
{
}
