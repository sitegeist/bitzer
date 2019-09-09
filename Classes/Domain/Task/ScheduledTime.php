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
            throw new \InvalidArgumentException('Given scheduled time is invalid, must be in format 2004-02-12T15:19:21+02:00, ' . $dateString . ' given', 1568033626);
        }

        return $scheduledTime;
    }

    /**
     * @param array $dateArray
     * @return \DateTimeImmutable
     * @todo properly determine time zone
     */
    public static function createFromArray(array $dateArray): \DateTimeImmutable
    {
        if (!isset($dateArray['date'])) {
            throw new \InvalidArgumentException('The scheduled date is mandatory but was not given in  ' . json_encode($dateArray), 1568033617);
        }
        $time = '00:00:00';
        if (isset($dateArray['time']) && !empty($dateArray['time'])) {
            $time = $dateArray['time'];
        }
        return self::createFromString($dateArray['date'] . 'T' . $time . '+00:00');
    }

    public static function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }

    public static function createFromDatabaseValue(string $dateString): \DateTimeImmutable
    {
        $scheduledTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateString);

        return $scheduledTime;
    }
}
