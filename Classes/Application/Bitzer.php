<?php declare(strict_types=1);
namespace Sitegeist\Bitzer\Application;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Psr\Http\Message\UriInterface;
use Sitegeist\Bitzer\Domain\Agent\Agent;
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
use Sitegeist\Bitzer\Infrastructure\ContentContextFactory;

/**
 * The central command handler as an application service
 *
 * Takes commands, validates them and relays them to the schedule
 *
 * @Flow\Scope("singleton")
 * @api
 */
final class Bitzer
{
    private Schedule $schedule;

    private AgentRepository $agentRepository;

    private ContentContextFactory $contentContextFactory;

    /**
     * The constraint check plugins, indexed by task type
     * @var array<string,array<int,ConstraintCheckPluginInterface>>
     */
    private array $constraintCheckPlugins;

    public function __construct(
        Schedule $schedule,
        AgentRepository $agentRepository,
        ContentContextFactory $contentContextFactory,
        ObjectManagerInterface $objectManager,
        array $constraintCheckPluginMapping
    ) {
        $this->schedule = $schedule;
        $this->agentRepository = $agentRepository;
        $this->contentContextFactory = $contentContextFactory;
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

                    $constraintCheckPlugins[$taskClassName][] = $objectManager->get($pluginClassName);
                }
            }
        }
        $this->constraintCheckPlugins = $constraintCheckPlugins;
    }


    final public function handleScheduleTask(ScheduleTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToNotExist($command->getIdentifier(), $constraintCheckResult);
        $this->requireAgentToExist($command->getAgent(), $constraintCheckResult);
        $this->requireScheduledTimeToBeSet($command->getScheduledTime(), $constraintCheckResult);
        $this->requireDescriptionToBeSet($command->getProperties(), $constraintCheckResult);
        if ($command->getObject()) {
            // @todo find some way to enforce this; recently published nodes are not yet known to the new content context
            //$this->requireObjectToExist($command->getObject(), $command->getAgent(), $constraintCheckResult);
        }
        if ($command->getTarget()) {
            $this->requireTargetToBeAbsoluteUri($command->getTarget(), $constraintCheckResult);
        }

        foreach ($this->getConstraintCheckPlugins($command->getClassName()) as $constraintCheckPlugin) {
            $constraintCheckPlugin->checkScheduleTask($command, $constraintCheckResult);
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->scheduleTask($command);
        }
    }

    final public function handleRescheduleTask(RescheduleTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);
        $this->requireScheduledTimeToBeSet($command->getScheduledTime(), $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->getIdentifier());
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkRescheduleTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->rescheduleTask($command->getIdentifier(), $command->getScheduledTime());
        }
    }

    final public function handleReassignTask(ReassignTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);
        $this->requireAgentToExist($command->getAgent(), $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->getIdentifier());
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkReassignTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->reassignTask($command->getIdentifier(), $command->getAgent());
        }
    }

    final public function handleSetNewTaskTarget(SetNewTaskTarget $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);
        if ($command->getTarget()) {
            $this->requireTargetToBeAbsoluteUri($command->getTarget(), $constraintCheckResult);
        }

        $task = $this->schedule->findByIdentifier($command->getIdentifier());
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkSetNewTaskTarget($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->setTaskTarget($command->getIdentifier(), $command->getTarget());
        }
    }

    final public function handleSetNewTaskObject(SetNewTaskObject $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);
        if ($command->getObject()) {
            $this->requireObjectToExist($command->getObject(), $constraintCheckResult);
        }

        $task = $this->schedule->findByIdentifier($command->getIdentifier());
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkSetNewTaskObject($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->setTaskObject($command->getIdentifier(), $command->getObject());
        }
    }

    final public function handleSetTaskProperties(SetTaskProperties $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);
        $this->requireDescriptionToBeSet($command->getProperties(), $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->getIdentifier());
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkSetTaskProperties($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->setTaskProperties($command->getIdentifier(), $command->getProperties());
        }
    }

    final public function handleCancelTask(CancelTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->getIdentifier());
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkCancelTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->cancelTask($command->getIdentifier());
        }
    }

    final public function handleActivateTask(ActivateTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->getIdentifier());
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkActivateTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->updateTaskActionStatus($command->getIdentifier(), ActionStatusType::active());
        }
    }

    final public function handleCompleteTask(CompleteTask $command, ?ConstraintCheckResult $constraintCheckResult = null): void
    {
        $this->requireTaskToExist($command->getIdentifier(), $constraintCheckResult);

        $task = $this->schedule->findByIdentifier($command->getIdentifier());
        if ($task) {
            foreach ($this->getConstraintCheckPlugins(TaskClassName::createFromObject($task)) as $constraintCheckPlugin) {
                $constraintCheckPlugin->checkCompleteTask($command, $constraintCheckResult);
            }
        }

        if (IsCommandToBeExecuted::isSatisfiedByConstraintCheckResult($constraintCheckResult)) {
            $this->schedule->updateTaskActionStatus($command->getIdentifier(), ActionStatusType::completed());
        }
    }

    private function requireTaskToExist(TaskIdentifier $identifier, ConstraintCheckResult $constraintCheckResult = null): void
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

    private function requireTaskToNotExist(TaskIdentifier $identifier, ConstraintCheckResult $constraintCheckResult = null): void
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

    private function requireScheduledTimeToBeSet(?\DateTimeImmutable $scheduledTime, ConstraintCheckResult $constraintCheckResult = null): void
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

    private function requireAgentToExist(Agent $agent, ConstraintCheckResult $constraintCheckResult = null): void
    {
        if (!$this->agentRepository->findByIdentifier($agent->getIdentifier())) {
            $exception = AgentDoesNotExist::althoughExpectedForIdentifier($agent->getIdentifier()->toString());
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('agent', $exception, [$agent->getIdentifier()->toString()]);
            } else {
                throw $exception;
            }
        }
    }

    private function requireObjectToExist(NodeAddress $address, ConstraintCheckResult $constraintCheckResult = null): void
    {
        $contentContext = $this->contentContextFactory->createContentContext($address);

        if (!$contentContext->getNodeByIdentifier((string) $address->getNodeAggregateIdentifier())) {
            $exception = ObjectDoesNotExist::althoughExpectedForAddress($address);
            if ($constraintCheckResult) {
                $constraintCheckResult->registerFailedCheck('object', $exception, [$address->getNodeAggregateIdentifier(), $address->getWorkspaceName(),$address->getDimensionSpacePoint()]);
            } else {
                throw $exception;
            }
        }
    }

    private function requireTargetToBeAbsoluteUri(UriInterface $target, ConstraintCheckResult $constraintCheckResult = null): void
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

    private function requireDescriptionToBeSet(array $properties, ConstraintCheckResult $constraintCheckResult = null): void
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
