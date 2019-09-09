<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if a scheduled time is undefined but was supposed to be
 */
final class ScheduledTimeIsUndefined extends \DomainException
{
}
