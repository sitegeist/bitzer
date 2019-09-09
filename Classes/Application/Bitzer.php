<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Application;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Command\ReassignTask;
use Sitegeist\Bitzer\Domain\Task\Command\RescheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\SetTaskProperties;
use Sitegeist\Bitzer\Domain\Task\ConstraintCheckResult;
use Sitegeist\Bitzer\Domain\Task\Exception\AgentDoesNotExist;
use Sitegeist\Bitzer\Domain\Task\Exception\DescriptionIsInvalid;
use Sitegeist\Bitzer\Domain\Task\Exception\ObjectDoesNotExist;
use Sitegeist\Bitzer\Domain\Task\Exception\ScheduledTimeIsUndefined;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\Exception\TaskDoesExist;
use Sitegeist\Bitzer\Domain\Task\Exception\TaskDoesNotExist;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Infrastructure\ContentContextFactory;

/**
 * The central command handler as an application service
 *
 * Takes commands, validates them and relays them to the schedule
 *
 * @Flow\Scope("singleton")
 * @api
 */
class Bitzer
{
    /**
     * @Flow\Inject
     * @var Schedule
     */
    protected $schedule;

    /**
     * @Flow\Inject
     * @var AgentRepository
     */
    protected $agentRepository;

    /**
     * @Flow\Inject
     * @var ContentContextFactory
     */
    protected $contentContextFactory;

    final public function handleScheduleTask(ScheduleTask $command, ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToNotExist($command->getIdentifier(), $constraintCheckResult);
        $this->requireAgentToExist($command->getAgent(), $constraintCheckResult);
        $this->requireScheduledTimeToBeSet($command->getScheduledTime(), $constraintCheckResult);
        $this->requireDescriptionToBeSet($command->getProperties(), $constraintCheckResult);
        if ($command->getObject()) {
            $this->requireObjectToExist($command->getObject(), $constraintCheckResult);
        }

        if (!$constraintCheckResult || $constraintCheckResult->hasSucceeded()) {
            $this->schedule->scheduleTask($command);
        }
    }

    final public function handleRescheduleTask(RescheduleTask $command): void
    {
        $this->requireTaskToExist($command->getIdentifier());

        $this->schedule->rescheduleTask($command->getIdentifier(), $command->getScheduledTime());
    }

    final public function handleReassignTask(ReassignTask $command, ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);
        $this->requireAgentToExist($command->getAgent(), $constraintCheckResult);

        if (!$constraintCheckResult || $constraintCheckResult->hasSucceeded()) {
            $this->schedule->reassignTask($command->getIdentifier(), $command->getAgent());
        }
    }

    final public function handleSetTaskProperties(SetTaskProperties $command, ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);
        $this->requireDescriptionToBeSet($command->getProperties(), $constraintCheckResult);

        if (!$constraintCheckResult || $constraintCheckResult->hasSucceeded()) {
            $this->schedule->setTaskProperties($command->getIdentifier(), $command->getProperties());
        }
    }

    final public function handleCancelTask(CancelTask $command): void
    {
        $this->requireTaskToExist($command->getIdentifier());

        $this->schedule->cancelTask($command->getIdentifier());
    }

    final public function handleCompleteTask(CompleteTask $command): void
    {
        $this->requireTaskToExist($command->getIdentifier());

        $this->schedule->updateTaskActionStatus($command->getIdentifier(), ActionStatusType::completed());
    }

    private function requireTaskToExist(TaskIdentifier $identifier, ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!$this->schedule->findByIdentifier($identifier)) {
            $exception = new TaskDoesNotExist('No task with identifier ' . $identifier . ' exists.', 1567600174);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('identifier', $exception);
            } else {
                throw $exception;
            }
        }
    }

    private function requireTaskToNotExist(TaskIdentifier $identifier, ConstraintCheckResult $constraintCheckResult = null): void
    {
        if ($this->schedule->findByIdentifier($identifier)) {
            $exception = new TaskDoesExist('Task with identifier ' . $identifier . ' already exists.', 1567600184);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('identifier', $exception);
            } else {
                throw $exception;
            }
        }
    }

    private function requireScheduledTimeToBeSet(?\DateTimeImmutable $scheduledTime, ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!$scheduledTime) {
            $exception = new ScheduledTimeIsUndefined('Scheduled time is undefined', 1568033796);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('scheduledTime', $exception);
            } else {
                throw $exception;
            }
        }
    }

    private function requireAgentToExist(string $agentIdentifier, ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!$this->agentRepository->findByIdentifier($agentIdentifier)) {
            $exception = new AgentDoesNotExist('No agent with identifier ' . $agentIdentifier . ' exists', 1567602522);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('agent', $exception);
            } else {
                throw $exception;
            }
        }
    }

    private function requireObjectToExist(NodeAddress $address, ConstraintCheckResult $constraintCheckResult = null): void
    {
        $contentContext = $this->contentContextFactory->createContentContext($address);

        if (!$contentContext->getNodeByIdentifier((string) $address->getNodeAggregateIdentifier())) {
            $exception = new ObjectDoesNotExist('No node with identifier ' . $address->getNodeAggregateIdentifier() . ' could be found in workspace ' . $address->getWorkspaceName() . ' and dimension space point ' . $address->getDimensionSpacePoint(), 1567603391);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('object', $exception);
            } else {
                throw $exception;
            }
        }
    }

    private function requireDescriptionToBeSet(array $properties, ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!isset($properties['description']) || empty($properties['description'])) {
            $exception = new DescriptionIsInvalid('The description of a task must not be empty.', 1567764586);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('properties.description', $exception);
            } else {
                throw $exception;
            }
        }
    }
}
