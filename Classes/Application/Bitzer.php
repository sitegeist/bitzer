<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Application;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\ActionStatusType;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Command\RescheduleTask;
use Sitegeist\Bitzer\Domain\Task\Exception\AgentDoesNotExist;
use Sitegeist\Bitzer\Domain\Task\Exception\ObjectDoesNotExist;
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

    final public function handleScheduleTask(ScheduleTask $command): void
    {
        $this->requireTaskToNotExist($command->getIdentifier());
        $this->requireAgentToExist($command->getAgent());
        if ($command->getObject()) {
            $this->requireObjectToExist($command->getObject());
        }

        $this->schedule->scheduleTask($command);
    }

    final public function handleRescheduleTask(RescheduleTask $command): void
    {
        $this->requireTaskToExist($command->getIdentifier());

        $this->schedule->rescheduleTask($command->getIdentifier(), $command->getScheduledTime());
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

    private function requireTaskToExist(TaskIdentifier $identifier): void
    {
        if (!$this->schedule->findByIdentifier($identifier)) {
            throw new TaskDoesNotExist('No task with identifier ' . $identifier . ' exists.', 1567600174);
        }
    }

    private function requireTaskToNotExist(TaskIdentifier $identifier): void
    {
        if ($this->schedule->findByIdentifier($identifier)) {
            throw new TaskDoesExist('Task with identifier ' . $identifier . ' already exists.', 1567600184);
        }
    }

    private function requireAgentToExist(string $agentIdentifier): void
    {
        if (!$this->agentRepository->findByIdentifier($agentIdentifier)) {
            throw new AgentDoesNotExist('No agent with identifier ' . $agentIdentifier . ' exists', 1567602522);
        }
    }

    private function requireObjectToExist(NodeAddress $address): void
    {
        $contentContext = $this->contentContextFactory->createContentContext($address);

        if (!$contentContext->getNodeByIdentifier((string) $address->getNodeAggregateIdentifier())) {
            throw new ObjectDoesNotExist('No node with identifier ' . $address->getNodeAggregateIdentifier() . ' could be found in workspace ' . $address->getWorkspaceName() . ' and dimension space point ' . $address->getDimensionSpacePoint(), 1567603391);
        }
    }
}
