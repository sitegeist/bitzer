<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Application\Controller;

use Neos\Error\Messages\Message;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Backend\ModuleController;
use Sitegeist\Bitzer\Application\Bitzer;
use Sitegeist\Bitzer\Domain\Agent\AgentRepository;
use Sitegeist\Bitzer\Domain\Task\Command\CancelTask;
use Sitegeist\Bitzer\Domain\Task\Command\CompleteTask;
use Sitegeist\Bitzer\Domain\Task\Command\ReassignTask;
use Sitegeist\Bitzer\Domain\Task\Command\RescheduleTask;
use Sitegeist\Bitzer\Domain\Task\Schedule;
use Sitegeist\Bitzer\Domain\Task\TaskIdentifier;

/**
 * The bitzer controller for schedule actions
 */
class BitzerController extends ModuleController
{
    /**
     * @Flow\Inject
     * @var Bitzer
     */
    protected $bitzer;

    /**
     * @Flow\Inject
     * @var Schedule
     */
    protected $schedule;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var AgentRepository
     */
    protected $agentRepository;

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @Flow\InjectConfiguration(path="upcomingInterval")
     * @var string
     */
    protected $upcomingInterval;

    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @var FusionView
     */
    protected $view;

    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
    }

    public function indexAction(array $module = []): void
    {
        if ($this->securityContext->hasRole('Sitegeist.Bitzer:Administrator')) {
            $this->forward('schedule');
        } else {
            $this->forward('mySchedule');
        }
    }

    public function scheduleAction(): void
    {
        $tasks = $this->schedule->findAllOrdered();
        $this->view->setFusionPath('schedule');
        $this->view->assignMultiple([
            'tasks' => $tasks,
            'flashMessages' => $this->flashMessageContainer->getMessagesAndFlush()
        ]);
    }

    public function myScheduleAction(): void
    {
        $agents = $this->agentRepository->findCurrent();
        $groupedTasks = $this->schedule->findPastDueDueAndUpcoming(new \DateInterval($this->upcomingInterval), $agents);

        $this->view->setFusionPath('mySchedule');
        $this->view->assignMultiple([
            'groupedTasks' => $groupedTasks,
            'flashMessages' => $this->flashMessageContainer->getMessagesAndFlush()
        ]);
    }

    public function editTaskAction(TaskIdentifier $taskIdentifier): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);
        if (!$task) {
            $this->addFlashMessage($this->getLabel('editTask.taskWasNotFound', [$task->getDescription()]), '', Message::SEVERITY_WARNING);
            $this->redirect('index');
        }
        $this->view->setFusionPath('editTask');
        $this->view->assignMultiple([
            'task' => $task,
            'agents' => $this->agentRepository->findAll(),
            'flashMessages' => $this->flashMessageContainer->getMessagesAndFlush()
        ]);
    }

    public function rescheduleTaskAction(TaskIdentifier $taskIdentifier, \DateTimeImmutable $scheduledTime): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $command = new RescheduleTask($taskIdentifier, $scheduledTime);
        $this->bitzer->handleRescheduleTask($command);

        $this->addFlashMessage($this->getLabel('rescheduleTask.success', [$task->getDescription(), $scheduledTime->format('c')]), '');
        $this->redirect('editTask', null, null, ['taskIdentifier' => (string)$taskIdentifier]);
    }

    public function reassignTaskAction(TaskIdentifier $taskIdentifier, string $agent): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $command = new ReassignTask($taskIdentifier, $agent);
        $this->bitzer->handleReassignTask($command);

        $this->addFlashMessage($this->getLabel('reassignTask.success', [$task->getDescription(), $agent]), '');
        $this->redirect('editTask', null, null, ['taskIdentifier' => (string)$taskIdentifier]);
    }

    public function completeTaskAction(TaskIdentifier $taskIdentifier): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $command = new CompleteTask($taskIdentifier);
        $this->bitzer->handleCompleteTask($command);

        $this->addFlashMessage($this->getLabel('completeTask.success', [$task->getDescription()]), '');
        $this->redirect('index');
    }

    public function cancelTaskAction(TaskIdentifier $taskIdentifier): void
    {
        $task = $this->schedule->findByIdentifier($taskIdentifier);

        $command = new CancelTask($taskIdentifier);
        $this->bitzer->handleCancelTask($command);

        $this->addFlashMessage($this->getLabel('cancelTask.success', [$task->getDescription()]), '');
        $this->redirect('index');
    }

    private function getLabel(string $labelIdentifier, array $arguments = [], $quantity = null): ?string
    {
        return $this->translator->translateById($labelIdentifier, $arguments, $quantity, null, 'Module.Bitzer', 'Sitegeist.Bitzer');
    }
}
