<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Domain\Task;

enum TaskDueStatusType: string
{
    case STATUS_UPCOMING = 'upcoming';
    case STATUS_DUE = 'due';
    case STATUS_PAST_DUE = 'pastDue';

    public static function forTask(TaskInterface $task): TaskDueStatusType
    {
        $now = new \DateTimeImmutable();

        if ($task->getScheduledTime()->format('Y-m-d') === $now->format('Y-m-d')) {
            return self::STATUS_DUE;
        } else {
            return $task->getScheduledTime()->format('Y-m-d') > $now->format('Y-m-d')
                ? self::STATUS_UPCOMING
                : self::STATUS_PAST_DUE;
        }
    }
}
