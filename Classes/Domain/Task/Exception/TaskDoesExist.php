<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if a requested task exists but is not supposed to
 */
final class TaskDoesExist extends \DomainException
{
}
