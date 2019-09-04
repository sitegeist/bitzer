<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Command;

use Neos\Flow\Cli\CommandController;
use Neos\Flow\Reflection\ReflectionService;
use Psr\Http\Message\UriInterface;
use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Application\Bitzer;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\ScheduledTime;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The schedule, the repository for tasks
 */
class BitzerCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var Schedule
     */
    protected $schedule;

    /**
     * @Flow\Inject
     * @var Bitzer
     */
    protected $bitzer;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    public function listTasksCommand(): void
    {
        foreach ($this->schedule->findAll() as $task) {
            \Neos\Flow\var_dump($task->getIdentifier() . ' : ' . $task->getDescription());
        }
    }

    public function scheduleTaskCommand(
        string $shortType,
        string $description,
        string $scheduledTime,
        string $agent,
        ?NodeAddress $object = null,
        ?UriInterface $target = null
    ) {
        $command = new ScheduleTask(
            TaskIdentifier::create(),
            TaskClassName::fromShortType($shortType, $this->reflectionService),
            $description,
            ScheduledTime::createFromString($scheduledTime),
            $agent,
            $object,
            $target
        );

        $this->bitzer->handleScheduleTask($command);
    }

    public function cancelTaskCommand(TaskIdentifier $taskIdentifier): void
    {
        $command = new CancelTask($taskIdentifier);

        $this->bitzer->handleCancelTask($command);
    }
}
