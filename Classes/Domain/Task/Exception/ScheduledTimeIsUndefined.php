<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task\Exception;

/**
 * The exception to be thrown if a scheduled time is undefined but was supposed to be
 */
final class ScheduledTimeIsUndefined extends \DomainException
{
    public static function althoughExpected(): self
    {
        return new self('Scheduled time is undefined.', 1568033796);
    }
}
