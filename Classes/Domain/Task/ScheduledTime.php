<?php
declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The scheduled time factory for \DateTimeImmutable
 *
 * To be replaced by the actual node address from the new content repository
 * @Flow\Proxy(false)
 */
final class ScheduledTime
{
    public static function createFromString(string $dateString): \DateTimeImmutable
    {
        $scheduledTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $dateString);
        if (!$scheduledTime) {
            throw new \InvalidArgumentException('Given scheduled time is invalid, must be in format 2004-02-12T15:19:21+02:00, ' . $dateString . ' given');
        }

        return $scheduledTime;
    }

    public static function createFromDatabaseValue(string $dateString): \DateTimeImmutable
    {
        $scheduledTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateString);

        return $scheduledTime;
    }
}
