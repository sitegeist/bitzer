<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Domain\Task;

use Neos\Flow\Annotations as Flow;

/**
 * The value object describing a task's due status
 * @Flow\Proxy(false)
 */
final class TaskDueStatusType
{
    const STATUS_UPCOMING = 'upcoming';
    const STATUS_DUE = 'due';
    const STATUS_PAST_DUE = 'pastDue';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function upcoming(): TaskDueStatusType
    {
        return new self(self::STATUS_UPCOMING);
    }

    public static function due(): TaskDueStatusType
    {
        return new self(self::STATUS_DUE);
    }

    public static function pastDue(): TaskDueStatusType
    {
        return new self(self::STATUS_PAST_DUE);
    }

    public static function forTask(TaskInterface $task): TaskDueStatusType
    {
        $now = new \DateTimeImmutable();

        if ($task->getScheduledTime()->format('Y-m-d') === $now->format('Y-m-d')) {
            return self::due();
        } else {
            return $task->getScheduledTime()->format('Y-m-d') > $now->format('Y-m-d')
                ? self::upcoming()
                : self::pastDue();
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
