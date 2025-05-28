<?php

declare(strict_types=1);

namespace Sitegeist\Bitzer\Application;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Agent\Agent;
use Sitegeist\Bitzer\Domain\Agent\AgentIdentifier;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\Command\ActivateTask;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Command\ReassignTask;
use Sitegeist\Bitzer\Domain\Task\Command\RescheduleTask;
use Sitegeist\Bitzer\Domain\Task\Command\SetNewTaskObject;
use Sitegeist\Bitzer\Domain\Task\Command\SetNewTaskTarget;
use Sitegeist\Bitzer\Domain\Task\Command\SetTaskProperties;
use Sitegeist\Bitzer\Domain\Task\ConstraintCheckPluginInterface;
use Sitegeist\Bitzer\Domain\Task\ConstraintCheckResult;
use Sitegeist\Bitzer\Domain\Task\Exception\AgentDoesNotExist;
use Sitegeist\Bitzer\Domain\Task\Exception\ConstraintCheckPluginIsInvalid;
use Sitegeist\Bitzer\Domain\Task\Exception\DescriptionIsInvalid;
use Sitegeist\Bitzer\Domain\Task\Exception\ObjectDoesNotExist;
use Sitegeist\Bitzer\Domain\Task\Exception\ScheduledTimeIsUndefined;
use Sitegeist\Bitzer\Domain\Task\Exception\TargetIsInvalid;
use Sitegeist\Bitzer\Domain\Task\IsCommandToBeExecuted;
use Sitegeist\Bitzer\Domain\Task\NodeAddress;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\Command\ScheduleTask;
use Sitegeist\Bitzer\Domain\Task\Exception\TaskDoesExist;
use Sitegeist\Bitzer\Domain\Task\Exception\TaskDoesNotExist;
use Sitegeist\Bitzer\Domain\Task\TaskClassName;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;
use Sitegeist\Bitzer\Domain\Task\TaskInterface;
use Sitegeist\Bitzer\Infrastructure\ContentContextFactory;

/**
 * The central command handler as an application service
 *
 * Takes commands, validates them and relays them to the schedule
 *
 * @api
 */
#[Flow\Scope('singleton')]
final class Bitzer
{
    /**
     * The constraint check plugins, indexed by task type
     * @var array<class-string<TaskInterface>,array<int,ConstraintCheckPluginInterface>>
     */
    private readonly array $constraintCheckPlugins;

    /**
     * @param array<class-string<TaskInterface>,array<class-string<ConstraintCheckPluginInterface>,bool>> $constraintCheckPluginMapping
     */
    public function __construct(
        private readonly Schedule $schedule,
        private readonly AgentRepository $agentRepository,
        private readonly ContentContextFactory $contentContextFactory,
        ObjectManagerInterface $objectManager,
        array $constraintCheckPluginMapping
    ) {
        $constraintCheckPlugins = [];
        foreach ($constraintCheckPluginMapping as $taskClassName => $constraintCheckPluginNames) {
            foreach ($constraintCheckPluginNames as $pluginClassName => $isActive) {
                if ($isActive) {
                    if (!class_exists($pluginClassName)) {
                        throw ConstraintCheckPluginIsInvalid::becauseItIsNotImplemented($pluginClassName);
                    }
                    if (!in_array(ConstraintCheckPluginInterface::class, class_implements($pluginClassName))) {
                        throw ConstraintCheckPluginIsInvalid::becauseItDoesNotImplementTheRequiredInterface($pluginClassName);
                    }

                    /** @var ConstraintCheckPluginInterface $plugin */
                    $plugin = $objectManager->get($pluginClassName);
                    $constraintCheckPlugins[$taskClassName][] = $plugin;
                }
            }
        }
        $this->constraintCheckPlugins = $constraintCheckPlugins;
    }


    final public function handleScheduleTask(ScheduleTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToNotExist($command->identifier, $constraintCheckResult);
        $agent = $this->requireAgent($command->agentId, $constraintCheckResult);
        $this->requireScheduledTimeToBeSet($command->scheduledTime, $constraintCheckResult);
        $this->requireDescriptionToBeSet($command->properties, $constraintCheckResult);
        if ($command->object) {
            // @todo find some way to enforce this; recently published nodes are not yet known to the new content context
            //$this->requireObjectToExist($command->getObject(), $command->getAgent(), $constraintCheckResult);
        }
        if ($command->target) {
            $this->requireTargetToBeAbsoluteUri($command->target, $constraintCheckResult);
        }

        foreach ($this->getConstraintCheckPlugins($command->className) as $constraintCheckPlugin) {
            $constraintCheckPlugin->checkScheduleTask($command, $constraintCheckResult);
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult) && $agent) {
            $this->schedule->scheduleTask($command, $agent);
        }
    }

    final public function handleRescheduleTask(RescheduleTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->identifier, $constraintCheckResult);
        $this->requireScheduledTimeToBeSet($command->scheduledTime, $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->identifier);
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkRescheduleTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->rescheduleTask($command->identifier, $command->scheduledTime);
        }
    }

    final public function handleReassignTask(ReassignTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->identifier, $constraintCheckResult);
        $agent = $this->requireAgent($command->agentId, $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->identifier);
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkReassignTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult) && $agent) {
            $this->schedule->reassignTask($command->identifier, $agent);
        }
    }

    final public function handleSetNewTaskTarget(SetNewTaskTarget $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->identifier, $constraintCheckResult);
        if ($command->target) {
            $this->requireTargetToBeAbsoluteUri($command->target, $constraintCheckResult);
        }

        $task = $this->schedule->findByIdentifier($command->identifier);
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkSetNewTaskTarget($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->setTaskTarget($command->identifier, $command->target);
        }
    }

    final public function handleSetNewTaskObject(SetNewTaskObject $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->identifier, $constraintCheckResult);
        if ($command->object) {
            $this->requireObjectToExist($command->object, $constraintCheckResult);
        }

        $task = $this->schedule->findByIdentifier($command->identifier);
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkSetNewTaskObject($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->setTaskObject($command->identifier, $command->object);
        }
    }

    final public function handleSetTaskProperties(SetTaskProperties $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->identifier, $constraintCheckResult);
        $this->requireDescriptionToBeSet($command->properties, $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->identifier);
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkSetTaskProperties($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->setTaskProperties($command->identifier, $command->properties);
        }
    }

    final public function handleCancelTask(CancelTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->identifier, $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->identifier);
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkCancelTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->cancelTask($command->identifier);
        }
    }

    final public function handleActivateTask(ActivateTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->identifier, $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->identifier);
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkActivateTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->updateTaskActionStatus($command->identifier, ActionStatusType::TYPE_ACTIVE);
        }
    }

    final public function handleCompleteTask(CompleteTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->identifier, $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->identifier);
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkCompleteTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->updateTaskActionStatus($command->identifier, ActionStatusType::TYPE_COMPLETED);
        }
    }

    private function requireTaskToExist(TaskIdentifier $identifier, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!$this->schedule->findByIdentifier($identifier)) {
            $exception = TaskDoesNotExist::althoughExpectedForIdentifier($identifier);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('identifier', $exception, [$identifier]);
            } else {
                throw $exception;
            }
        }
    }

    private function requireTaskToNotExist(TaskIdentifier $identifier, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        if ($this->schedule->findByIdentifier($identifier)) {
            $exception = TaskDoesExist::althoughNotExpectedForIdentifier($identifier);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('identifier', $exception, [$identifier]);
            } else {
                throw $exception;
            }
        }
    }

    private function requireScheduledTimeToBeSet(?\DateTimeImmutable $scheduledTime, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!$scheduledTime) {
            $exception = ScheduledTimeIsUndefined::althoughExpected();
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('scheduledTime', $exception);
            } else {
                throw $exception;
            }
        }
    }

    private function requireAgent(AgentIdentifier $agentId, ?ConstraintCheckResult $constraintCheckResult = null): ?Agent
    {
        $agent = $this->agentRepository->findByIdentifier($agentId);
        if (!$agent) {
            $exception = AgentDoesNotExist::althoughExpectedForIdentifier($agentId->toString());
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('agent', $exception, [$agentId->toString()]);
                return null;
            } else {
                throw $exception;
            }
        } else {
            return $agent;
        }
    }

    private function requireObjectToExist(NodeAddress $address, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $contentContext = $this->contentContextFactory->createContentContext($address);

        if (!$contentContext->getNodeByIdentifier((string)$address->nodeAggregateIdentifier)) {
            $exception = ObjectDoesNotExist::althoughExpectedForAddress($address);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('object', $exception, [$address->nodeAggregateIdentifier, $address->workspaceName, $address->dimensionSpacePoint]);
            } else {
                throw $exception;
            }
        }
    }

    private function requireTargetToBeAbsoluteUri(UriInterface $target, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!$target->getHost()) {
            $exception = TargetIsInvalid::mustBeAnAbsoluteUri();
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('target', $exception);
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @param array<string,mixed> $properties
     */
    private function requireDescriptionToBeSet(array $properties, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!isset($properties['description']) || empty($properties['description'])) {
            $exception = DescriptionIsInvalid::mustNotBeEmpty();
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('properties.description', $exception);
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @return array<int,ConstraintCheckPluginInterface>
     */
    private function getConstraintCheckPlugins(TaskClassName $taskClassName): array
    {
        return $this->constraintCheckPlugins[(string)$taskClassName] ?? [];
    }
}
