<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Command;

use Neos\Flow\Cli\CommandController;
use Neos\Flow\Http\Uri;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Application\Bitzer;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Command\ReassignTask;
use Sitegeist\Bitzer\Domain\Task\Command\RescheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\SetNewTaskTarget;
use Sitegeist\Bitzer\Domain\Task\Command\SetTaskProperties;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\ScheduledTime;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Symfony\Component\Console\Helper\Table;

/**
 * The command line endpoint for sending commands to Bitzer
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
        $table = new Table($this->output->getOutput());

        $rows = [];
        foreach ($this->schedule->findAllOrdered() as $task) {
            $rows[] = [
                $task->getIdentifier(),
                $task->getScheduledTime()->format('Y-m-d H:i:s'),
                $task::getShortType(),
                implode("\n", \mb_str_split(\mb_substr($task->getDescription(), 0, 90), 30)),
                $task->getAgent(),
                $task->getObject() ? $task->getObject()->getLabel() : '',
                $task->getTarget()
            ];
        }

        $table
            ->setHeaders(['Identifier', 'Scheduled Time', 'Class', 'Description', 'Agent', 'Object', 'Target'])
            ->setRows($rows);

        $table->render();
    }

    public function scheduleTaskCommand(
        string $shortType,
        string $properties,
        string $scheduledTime,
        string $agent,
        ?NodeAddress $object = null,
        ?Uri $target = null
    ): void {
        $command = new ScheduleTask(
            TaskIdentifier::create(),
            TaskClassName::fromShortType($shortType, $this->reflectionService),
            ScheduledTime::createFromString($scheduledTime),
            $agent,
            $object,
            $target,
            json_decode($properties, true)
        );

        $this->bitzer->handleScheduleTask($command);
    }

    public function rescheduleTaskCommand(
        TaskIdentifier $taskIdentifier,
        string $scheduledTime
    ): void {
        $command = new RescheduleTask(
            $taskIdentifier,
            ScheduledTime::createFromString($scheduledTime)
        );

        $this->bitzer->handleRescheduleTask($command);
    }

    public function reassignTaskCommand(
        TaskIdentifier $taskIdentifier,
        string $agent
    ): void {
        $command = new ReassignTask(
            $taskIdentifier,
            $agent
        );

        $this->bitzer->handleReassignTask($command);
    }

    public function setNewTaskTargetCommand(
        TaskIdentifier $taskIdentifier,
        Uri $target
    ): void {
        $command = new SetNewTaskTarget(
            $taskIdentifier,
            $target
        );

        $this->bitzer->handleSetNewTaskTarget($command);
    }

    public function setTaskPropertiesCommand(
        TaskIdentifier $taskIdentifier,
        string $properties
    ): void {
        $command = new SetTaskProperties(
            $taskIdentifier,
            json_decode($properties, true)
        );

        $this->bitzer->handleSetTaskProperties($command);
    }

    public function cancelTaskCommand(TaskIdentifier $taskIdentifier): void
    {
        $command = new CancelTask($taskIdentifier);

        $this->bitzer->handleCancelTask($command);
    }

    public function completeTaskCommand(TaskIdentifier $taskIdentifier): void
    {
        $command = new CompleteTask($taskIdentifier);

        $this->bitzer->handleCompleteTask($command);
    }
}
